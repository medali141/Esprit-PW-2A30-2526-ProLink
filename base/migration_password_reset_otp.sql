-- Colonnes OTP mot de passe oublié (15 min) — exécuter sur une base prolink existante
ALTER TABLE `user`
  ADD COLUMN `mdp_reset_otp_hash` varchar(255) DEFAULT NULL AFTER `mdp`,
  ADD COLUMN `mdp_reset_otp_expires` datetime DEFAULT NULL AFTER `mdp_reset_otp_hash`;
