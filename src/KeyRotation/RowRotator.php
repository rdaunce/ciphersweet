<?php
namespace ParagonIE\CipherSweet\KeyRotation;

use ParagonIE\CipherSweet\Contract\KeyRotationInterface;
use ParagonIE\CipherSweet\EncryptedRow;
use ParagonIE\CipherSweet\Exception\ArrayKeyException;
use ParagonIE\CipherSweet\Exception\CryptoOperationException;
use ParagonIE\CipherSweet\Exception\InvalidCiphertextException;

/**
 * Class RowRotator
 * @package ParagonIE\CipherSweet\KeyRotation
 */
class RowRotator implements KeyRotationInterface
{
    /** @var EncryptedRow $old */
    protected $old;

    /** @var EncryptedRow $new */
    protected $new;

    /**
     * RowRotator constructor.
     * @param EncryptedRow $old
     * @param EncryptedRow $new
     */
    public function __construct(EncryptedRow $old, EncryptedRow $new)
    {
        $this->old = $old;
        $this->new = $new;
    }

    /**
     * @param string|array<string, string> $ciphertext
     * @return bool
     * @throws InvalidCiphertextException
     */
    public function needsReEncrypt($ciphertext = '')
    {
        if (!\is_array($ciphertext)) {
            throw new InvalidCiphertextException('RowRotator expects an array, not a string');
        }
        try {
            $this->new->decryptRow($ciphertext);
            return false;
        } catch (\Exception $ex) {
        }
        return true;
    }

    /**
     * @param string|array<string, string> $values
     * @return array
     * @throws CryptoOperationException
     * @throws InvalidCiphertextException
     * @throws ArrayKeyException
     * @throws \SodiumException
     */
    public function prepareForUpdate($values)
    {
        if (!\is_array($values)) {
            throw new InvalidCiphertextException('RowRotator expects an array, not a string');
        }
        /** @var array<string, string> $decrypted */
        $decrypted = $this->old->decryptRow($values);
        return $this->new->prepareRowForStorage($decrypted);
    }
}
