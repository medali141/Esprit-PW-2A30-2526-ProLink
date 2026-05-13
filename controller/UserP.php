<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/User.php';

class UserP {
    private ?bool $hasPaymentEmailColumn = null;
    private ?bool $hasFinancialAccountNameColumn = null;
    private ?bool $hasFinancialAccountNumberColumn = null;
    private ?bool $hasFinancialBankNameColumn = null;
    private ?bool $hasTotpSecretColumn = null;
    private ?bool $hasFaceIdCredentialIdColumn = null;
    private ?bool $hasFaceIdEnabledColumn = null;
    private ?bool $hasFacePhotoHashColumn = null;
    private ?bool $hasFacePhotoEnabledColumn = null;

    /** Liste des utilisateurs */
    public function listUsers() {
        $this->ensureUserSecurityColumns();
        $sql = "SELECT * FROM user";
        $db = Config::getConnexion();
        try {
            return $db->query($sql);
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    /** Ajout utilisateur */
    public function addUser($user) {
        $this->ensureUserSecurityColumns();
        $columns = "nom, prenom, email, mdp, type, age";
        $values = ":nom, :prenom, :email, :mdp, :type, :age";
        if ($this->hasPaymentEmailColumn()) {
            $columns .= ", payment_email";
            $values .= ", :payment_email";
        }
        if ($this->hasFinancialAccountNameColumn()) {
            $columns .= ", financial_account_name";
            $values .= ", :financial_account_name";
        }
        if ($this->hasFinancialAccountNumberColumn()) {
            $columns .= ", financial_account_number";
            $values .= ", :financial_account_number";
        }
        if ($this->hasFinancialBankNameColumn()) {
            $columns .= ", financial_bank_name";
            $values .= ", :financial_bank_name";
        }
        if ($this->hasTotpSecretColumn()) {
            $columns .= ", totp_secret";
            $values .= ", :totp_secret";
        }
        if ($this->hasFaceIdCredentialIdColumn()) {
            $columns .= ", face_id_credential_id";
            $values .= ", :face_id_credential_id";
        }
        if ($this->hasFaceIdEnabledColumn()) {
            $columns .= ", face_id_enabled";
            $values .= ", :face_id_enabled";
        }
        if ($this->hasFacePhotoHashColumn()) {
            $columns .= ", face_photo_hash";
            $values .= ", :face_photo_hash";
        }
        if ($this->hasFacePhotoEnabledColumn()) {
            $columns .= ", face_photo_enabled";
            $values .= ", :face_photo_enabled";
        }
        $sql = "INSERT INTO user ($columns) VALUES ($values)";
        $db = Config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $params = [
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom(),
                'email' => $user->getEmail(),
                'mdp' => password_hash($user->getMdp(), PASSWORD_DEFAULT),
                'type' => $user->getType(),
                'age' => $user->getAge()
            ];
            if ($this->hasPaymentEmailColumn()) {
                $paymentEmailValue = $user->getPaymentEmail();
                $paymentEmail = $paymentEmailValue === User::KEEP_VALUE ? '' : trim((string) $paymentEmailValue);
                $params['payment_email'] = $paymentEmail !== '' ? $paymentEmail : null;
            }
            if ($this->hasFinancialAccountNameColumn()) {
                $accountNameValue = $user->getFinancialAccountName();
                $accountName = $accountNameValue === User::KEEP_VALUE ? '' : trim((string) $accountNameValue);
                $params['financial_account_name'] = $accountName !== '' ? $accountName : null;
            }
            if ($this->hasFinancialAccountNumberColumn()) {
                $accountNumberValue = $user->getFinancialAccountNumber();
                $accountNumber = $accountNumberValue === User::KEEP_VALUE ? '' : trim((string) $accountNumberValue);
                $params['financial_account_number'] = $accountNumber !== '' ? $accountNumber : null;
            }
            if ($this->hasFinancialBankNameColumn()) {
                $bankNameValue = $user->getFinancialBankName();
                $bankName = $bankNameValue === User::KEEP_VALUE ? '' : trim((string) $bankNameValue);
                $params['financial_bank_name'] = $bankName !== '' ? $bankName : null;
            }
            if ($this->hasTotpSecretColumn()) {
                $totpSecretValue = $user->getTotpSecret();
                $totpSecret = $totpSecretValue === User::KEEP_VALUE ? '' : strtoupper(trim((string) $totpSecretValue));
                $params['totp_secret'] = $totpSecret !== '' ? $totpSecret : null;
            }
            if ($this->hasFaceIdCredentialIdColumn()) {
                $credentialIdValue = $user->getFaceIdCredentialId();
                $credentialId = $credentialIdValue === User::KEEP_VALUE ? '' : trim((string) $credentialIdValue);
                $params['face_id_credential_id'] = $credentialId !== '' ? $credentialId : null;
            }
            if ($this->hasFaceIdEnabledColumn()) {
                $faceEnabled = $user->getFaceIdEnabled();
                if ($faceEnabled === User::KEEP_VALUE) {
                    $faceEnabled = 0;
                }
                $params['face_id_enabled'] = (int) (!empty($faceEnabled) ? 1 : 0);
            }
            if ($this->hasFacePhotoHashColumn()) {
                $facePhotoHashValue = $user->getFacePhotoHash();
                $facePhotoHash = $facePhotoHashValue === User::KEEP_VALUE ? '' : trim((string) $facePhotoHashValue);
                $params['face_photo_hash'] = $facePhotoHash !== '' ? $facePhotoHash : null;
            }
            if ($this->hasFacePhotoEnabledColumn()) {
                $facePhotoEnabled = $user->getFacePhotoEnabled();
                if ($facePhotoEnabled === User::KEEP_VALUE) {
                    $facePhotoEnabled = 0;
                }
                $params['face_photo_enabled'] = (int) (!empty($facePhotoEnabled) ? 1 : 0);
            }
            $query->execute($params);
        } catch (PDOException $e) {
            if (isset($e->errorInfo[1]) && (int) $e->errorInfo[1] === 1062) {
                throw new RuntimeException('duplicate_email', 0, $e);
            }
            die('Error: ' . $e->getMessage());
        }
    }

    /** Suppression utilisateur */
    public function deleteUser($id) {
        $sql = "DELETE FROM user WHERE iduser = :id";
        $db = Config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id);

        try {
            $req->execute();
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    /** Indique si l'utilisateur a des commandes */
    public function hasCommandes($id) {
        $sql = "SELECT COUNT(*) as cnt FROM commande WHERE id_acheteur = :id";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
            $row = $query->fetch();
            return ((int) ($row['cnt'] ?? 0)) > 0;
        } catch (Exception $e) {
            // On error, be conservative and return true to prevent accidental deletion
            return true;
        }
    }

    /** Mise à jour utilisateur */
    public function updateUser($user, $id) {
        $this->ensureUserSecurityColumns();
        $db = Config::getConnexion();

        try {
            $setSql = "nom = :nom, prenom = :prenom, email = :email, type = :type, age = :age";
            $paymentEmailValue = $user->getPaymentEmail();
            $accountNameValue = $user->getFinancialAccountName();
            $accountNumberValue = $user->getFinancialAccountNumber();
            $bankNameValue = $user->getFinancialBankName();
            $totpSecretValue = $user->getTotpSecret();
            $faceIdCredentialValue = $user->getFaceIdCredentialId();
            $faceIdEnabledValue = $user->getFaceIdEnabled();
            $facePhotoHashValue = $user->getFacePhotoHash();
            $facePhotoEnabledValue = $user->getFacePhotoEnabled();
            if ($this->hasPaymentEmailColumn()) {
                if ($paymentEmailValue !== User::KEEP_VALUE) {
                    $setSql .= ", payment_email = :payment_email";
                }
            }
            if ($this->hasFinancialAccountNameColumn()) {
                if ($accountNameValue !== User::KEEP_VALUE) {
                    $setSql .= ", financial_account_name = :financial_account_name";
                }
            }
            if ($this->hasFinancialAccountNumberColumn()) {
                if ($accountNumberValue !== User::KEEP_VALUE) {
                    $setSql .= ", financial_account_number = :financial_account_number";
                }
            }
            if ($this->hasFinancialBankNameColumn()) {
                if ($bankNameValue !== User::KEEP_VALUE) {
                    $setSql .= ", financial_bank_name = :financial_bank_name";
                }
            }
            if ($this->hasTotpSecretColumn()) {
                if ($totpSecretValue !== User::KEEP_VALUE) {
                    $setSql .= ", totp_secret = :totp_secret";
                }
            }
            if ($this->hasFaceIdCredentialIdColumn()) {
                if ($faceIdCredentialValue !== User::KEEP_VALUE) {
                    $setSql .= ", face_id_credential_id = :face_id_credential_id";
                }
            }
            if ($this->hasFaceIdEnabledColumn()) {
                if ($faceIdEnabledValue !== User::KEEP_VALUE) {
                    $setSql .= ", face_id_enabled = :face_id_enabled";
                }
            }
            if ($this->hasFacePhotoHashColumn()) {
                if ($facePhotoHashValue !== User::KEEP_VALUE) {
                    $setSql .= ", face_photo_hash = :face_photo_hash";
                }
            }
            if ($this->hasFacePhotoEnabledColumn()) {
                if ($facePhotoEnabledValue !== User::KEEP_VALUE) {
                    $setSql .= ", face_photo_enabled = :face_photo_enabled";
                }
            }
            $query = $db->prepare("UPDATE user SET $setSql WHERE iduser = :id");

            $params = [
                'id' => $id,
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom(),
                'email' => $user->getEmail(),
                'type' => $user->getType(),
                'age' => $user->getAge()
            ];
            if ($this->hasPaymentEmailColumn()) {
                if ($paymentEmailValue !== User::KEEP_VALUE) {
                    $paymentEmail = trim((string) $paymentEmailValue);
                    $params['payment_email'] = $paymentEmail !== '' ? $paymentEmail : null;
                }
            }
            if ($this->hasFinancialAccountNameColumn()) {
                if ($accountNameValue !== User::KEEP_VALUE) {
                    $accountName = trim((string) $accountNameValue);
                    $params['financial_account_name'] = $accountName !== '' ? $accountName : null;
                }
            }
            if ($this->hasFinancialAccountNumberColumn()) {
                if ($accountNumberValue !== User::KEEP_VALUE) {
                    $accountNumber = trim((string) $accountNumberValue);
                    $params['financial_account_number'] = $accountNumber !== '' ? $accountNumber : null;
                }
            }
            if ($this->hasFinancialBankNameColumn()) {
                if ($bankNameValue !== User::KEEP_VALUE) {
                    $bankName = trim((string) $bankNameValue);
                    $params['financial_bank_name'] = $bankName !== '' ? $bankName : null;
                }
            }
            if ($this->hasTotpSecretColumn()) {
                if ($totpSecretValue !== User::KEEP_VALUE) {
                    $totpSecret = strtoupper(trim((string) $totpSecretValue));
                    $params['totp_secret'] = $totpSecret !== '' ? $totpSecret : null;
                }
            }
            if ($this->hasFaceIdCredentialIdColumn()) {
                if ($faceIdCredentialValue !== User::KEEP_VALUE) {
                    $credentialId = trim((string) $faceIdCredentialValue);
                    $params['face_id_credential_id'] = $credentialId !== '' ? $credentialId : null;
                }
            }
            if ($this->hasFaceIdEnabledColumn()) {
                if ($faceIdEnabledValue !== User::KEEP_VALUE) {
                    $params['face_id_enabled'] = (int) (!empty($faceIdEnabledValue) ? 1 : 0);
                }
            }
            if ($this->hasFacePhotoHashColumn()) {
                if ($facePhotoHashValue !== User::KEEP_VALUE) {
                    $facePhotoHash = trim((string) $facePhotoHashValue);
                    $params['face_photo_hash'] = $facePhotoHash !== '' ? $facePhotoHash : null;
                }
            }
            if ($this->hasFacePhotoEnabledColumn()) {
                if ($facePhotoEnabledValue !== User::KEEP_VALUE) {
                    $params['face_photo_enabled'] = (int) (!empty($facePhotoEnabledValue) ? 1 : 0);
                }
            }
            $query->execute($params);

        } catch (PDOException $e) {
            if (isset($e->errorInfo[1]) && (int) $e->errorInfo[1] === 1062) {
                throw new RuntimeException('duplicate_email', 0, $e);
            }
            die('Error:' . $e->getMessage());
        }
    }

    /** Détail utilisateur */
    public function showUser($id) {
        $this->ensureUserSecurityColumns();
        $sql = "SELECT * FROM user WHERE iduser = :id";
        $db = Config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    private function ensureUserSecurityColumns(): void {
        $this->hasPaymentEmailColumn();
        $this->hasFinancialAccountNameColumn();
        $this->hasFinancialAccountNumberColumn();
        $this->hasFinancialBankNameColumn();
        $this->hasTotpSecretColumn();
        $this->hasFaceIdCredentialIdColumn();
        $this->hasFaceIdEnabledColumn();
        $this->hasFacePhotoHashColumn();
        $this->hasFacePhotoEnabledColumn();
    }

    private function hasPaymentEmailColumn(): bool {
        if ($this->hasPaymentEmailColumn !== null) {
            return $this->hasPaymentEmailColumn;
        }
        $db = Config::getConnexion();
        $st = $db->query("SHOW COLUMNS FROM user LIKE 'payment_email'");
        $exists = (bool) $st->fetch(PDO::FETCH_ASSOC);
        if (!$exists) {
            try {
                $db->exec("ALTER TABLE user ADD COLUMN payment_email VARCHAR(150) NULL AFTER email");
                $exists = true;
            } catch (Throwable $e) {
                $exists = false;
            }
        }
        $this->hasPaymentEmailColumn = $exists;
        return $this->hasPaymentEmailColumn;
    }

    private function hasFinancialAccountNameColumn(): bool {
        if ($this->hasFinancialAccountNameColumn !== null) {
            return $this->hasFinancialAccountNameColumn;
        }
        $db = Config::getConnexion();
        $st = $db->query("SHOW COLUMNS FROM user LIKE 'financial_account_name'");
        $exists = (bool) $st->fetch(PDO::FETCH_ASSOC);
        if (!$exists) {
            try {
                $db->exec("ALTER TABLE user ADD COLUMN financial_account_name VARCHAR(120) NULL AFTER points_fidelite");
                $exists = true;
            } catch (Throwable $e) {
                $exists = false;
            }
        }
        $this->hasFinancialAccountNameColumn = $exists;
        return $this->hasFinancialAccountNameColumn;
    }

    private function hasFinancialAccountNumberColumn(): bool {
        if ($this->hasFinancialAccountNumberColumn !== null) {
            return $this->hasFinancialAccountNumberColumn;
        }
        $db = Config::getConnexion();
        $st = $db->query("SHOW COLUMNS FROM user LIKE 'financial_account_number'");
        $exists = (bool) $st->fetch(PDO::FETCH_ASSOC);
        if (!$exists) {
            try {
                $db->exec("ALTER TABLE user ADD COLUMN financial_account_number VARCHAR(80) NULL AFTER financial_account_name");
                $exists = true;
            } catch (Throwable $e) {
                $exists = false;
            }
        }
        $this->hasFinancialAccountNumberColumn = $exists;
        return $this->hasFinancialAccountNumberColumn;
    }

    private function hasFinancialBankNameColumn(): bool {
        if ($this->hasFinancialBankNameColumn !== null) {
            return $this->hasFinancialBankNameColumn;
        }
        $db = Config::getConnexion();
        $st = $db->query("SHOW COLUMNS FROM user LIKE 'financial_bank_name'");
        $exists = (bool) $st->fetch(PDO::FETCH_ASSOC);
        if (!$exists) {
            try {
                $db->exec("ALTER TABLE user ADD COLUMN financial_bank_name VARCHAR(120) NULL AFTER financial_account_number");
                $exists = true;
            } catch (Throwable $e) {
                $exists = false;
            }
        }
        $this->hasFinancialBankNameColumn = $exists;
        return $this->hasFinancialBankNameColumn;
    }

    private function hasTotpSecretColumn(): bool {
        if ($this->hasTotpSecretColumn !== null) {
            return $this->hasTotpSecretColumn;
        }
        $db = Config::getConnexion();
        $st = $db->query("SHOW COLUMNS FROM user LIKE 'totp_secret'");
        $exists = (bool) $st->fetch(PDO::FETCH_ASSOC);
        if (!$exists) {
            try {
                $db->exec("ALTER TABLE user ADD COLUMN totp_secret VARCHAR(64) NULL AFTER financial_bank_name");
                $exists = true;
            } catch (Throwable $e) {
                $exists = false;
            }
        }
        $this->hasTotpSecretColumn = $exists;
        return $this->hasTotpSecretColumn;
    }

    private function hasFaceIdCredentialIdColumn(): bool {
        if ($this->hasFaceIdCredentialIdColumn !== null) {
            return $this->hasFaceIdCredentialIdColumn;
        }
        $db = Config::getConnexion();
        $st = $db->query("SHOW COLUMNS FROM user LIKE 'face_id_credential_id'");
        $exists = (bool) $st->fetch(PDO::FETCH_ASSOC);
        if (!$exists) {
            try {
                $db->exec("ALTER TABLE user ADD COLUMN face_id_credential_id VARCHAR(255) NULL AFTER totp_secret");
                $exists = true;
            } catch (Throwable $e) {
                $exists = false;
            }
        }
        $this->hasFaceIdCredentialIdColumn = $exists;
        return $this->hasFaceIdCredentialIdColumn;
    }

    private function hasFaceIdEnabledColumn(): bool {
        if ($this->hasFaceIdEnabledColumn !== null) {
            return $this->hasFaceIdEnabledColumn;
        }
        $db = Config::getConnexion();
        $st = $db->query("SHOW COLUMNS FROM user LIKE 'face_id_enabled'");
        $exists = (bool) $st->fetch(PDO::FETCH_ASSOC);
        if (!$exists) {
            try {
                $db->exec("ALTER TABLE user ADD COLUMN face_id_enabled TINYINT(1) NOT NULL DEFAULT 0 AFTER face_id_credential_id");
                $exists = true;
            } catch (Throwable $e) {
                $exists = false;
            }
        }
        $this->hasFaceIdEnabledColumn = $exists;
        return $this->hasFaceIdEnabledColumn;
    }

    private function hasFacePhotoHashColumn(): bool {
        if ($this->hasFacePhotoHashColumn !== null) {
            return $this->hasFacePhotoHashColumn;
        }
        $db = Config::getConnexion();
        $st = $db->query("SHOW COLUMNS FROM user LIKE 'face_photo_hash'");
        $exists = (bool) $st->fetch(PDO::FETCH_ASSOC);
        if (!$exists) {
            try {
                $db->exec("ALTER TABLE user ADD COLUMN face_photo_hash VARCHAR(128) NULL AFTER face_id_enabled");
                $exists = true;
            } catch (Throwable $e) {
                $exists = false;
            }
        }
        $this->hasFacePhotoHashColumn = $exists;
        return $this->hasFacePhotoHashColumn;
    }

    private function hasFacePhotoEnabledColumn(): bool {
        if ($this->hasFacePhotoEnabledColumn !== null) {
            return $this->hasFacePhotoEnabledColumn;
        }
        $db = Config::getConnexion();
        $st = $db->query("SHOW COLUMNS FROM user LIKE 'face_photo_enabled'");
        $exists = (bool) $st->fetch(PDO::FETCH_ASSOC);
        if (!$exists) {
            try {
                $db->exec("ALTER TABLE user ADD COLUMN face_photo_enabled TINYINT(1) NOT NULL DEFAULT 0 AFTER face_photo_hash");
                $exists = true;
            } catch (Throwable $e) {
                $exists = false;
            }
        }
        $this->hasFacePhotoEnabledColumn = $exists;
        return $this->hasFacePhotoEnabledColumn;
    }
}