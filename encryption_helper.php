<?php
// encryption_helper.php
class EncryptionHelper {
    private $key;
    private $cipher = "aes-256-cbc";

    public function __construct() {
        // Use a secure key from environment or configuration
        $this->key = base64_decode($_ENV['ENCRYPTION_KEY']);
    }

    public function encrypt($data) {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->cipher));
        $encrypted = openssl_encrypt($data, $this->cipher, $this->key, 0, $iv);
        return [
            'data' => $encrypted,
            'iv' => bin2hex($iv)
        ];
    }

    public function decrypt($encryptedData, $iv) {
        return openssl_decrypt(
            $encryptedData,
            $this->cipher,
            $this->key,
            0,
            hex2bin($iv)
        );
    }
}