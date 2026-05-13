<?php
class User {
    public const KEEP_VALUE = '__KEEP__';
    private $iduser;
    private $nom;
    private $prenom;
    private $email;
    private $mdp;
    private $type;
    private $age;
    private $paymentEmail;
    private $financialAccountName;
    private $financialAccountNumber;
    private $financialBankName;
    private $totpSecret;
    private $faceIdCredentialId;
    private $faceIdEnabled;
    private $facePhotoHash;
    private $facePhotoEnabled;

    public function __construct(
        $nom,
        $prenom,
        $email,
        $mdp,
        $type,
        $age,
        $paymentEmail = self::KEEP_VALUE,
        $financialAccountName = self::KEEP_VALUE,
        $financialAccountNumber = self::KEEP_VALUE,
        $financialBankName = self::KEEP_VALUE,
        $totpSecret = self::KEEP_VALUE,
        $faceIdCredentialId = self::KEEP_VALUE,
        $faceIdEnabled = self::KEEP_VALUE,
        $facePhotoHash = self::KEEP_VALUE,
        $facePhotoEnabled = self::KEEP_VALUE
    ) {
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->email = $email;
        $this->mdp = $mdp;
        $this->type = $type;
        $this->age = $age;
        $this->paymentEmail = $paymentEmail;
        $this->financialAccountName = $financialAccountName;
        $this->financialAccountNumber = $financialAccountNumber;
        $this->financialBankName = $financialBankName;
        $this->totpSecret = $totpSecret;
        $this->faceIdCredentialId = $faceIdCredentialId;
        $this->faceIdEnabled = $faceIdEnabled;
        $this->facePhotoHash = $facePhotoHash;
        $this->facePhotoEnabled = $facePhotoEnabled;
    }

    public function getNom() { return $this->nom; }
    public function getPrenom() { return $this->prenom; }
    public function getEmail() { return $this->email; }
    public function getMdp() { return $this->mdp; }
    public function getType() { return $this->type; }
    public function getAge() { return $this->age; }
    public function getPaymentEmail() { return $this->paymentEmail; }
    public function getFinancialAccountName() { return $this->financialAccountName; }
    public function getFinancialAccountNumber() { return $this->financialAccountNumber; }
    public function getFinancialBankName() { return $this->financialBankName; }
    public function getTotpSecret() { return $this->totpSecret; }
    public function getFaceIdCredentialId() { return $this->faceIdCredentialId; }
    public function getFaceIdEnabled() { return $this->faceIdEnabled; }
    public function getFacePhotoHash() { return $this->facePhotoHash; }
    public function getFacePhotoEnabled() { return $this->facePhotoEnabled; }
}

// Concrete user types consolidated here to avoid multiple small files.
class Admin extends User {
    public function getType() {
        return "admin";
    }
}

class Candidat extends User {
    public function getType() {
        return "candidat";
    }
}

class Entrepreneur extends User {
    public function getType() {
        return "entrepreneur";
    }
}