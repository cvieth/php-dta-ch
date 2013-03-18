<?php

/**
 * Klasse zum Erzeugen von DTA Transaktionen im schweizer Six Interbank Clearing
 * Format.
 *
 * @author Christoph Vieth <cvieth@coreweb.de>
 */
class dtaChTransaction {

    const fillChar = ' ';

    /**
     * DTA-ID
     * @var string
     */
    private $dtaId = NULL;
    private $debitAccount = NULL;
    private $paymentAmount = NULL;

    public function test() {
        var_dump($this->genTA827());
    }

    private function isIsoCurrencyCode($currencyCode) {
        /**
         * @todo Weitere ISO-Währungscodes einpflegen
         */
        $validCodes = array('CHF', 'EUR');
        if (in_array($currencyCode, $validCodes))
            return TRUE;
        else
            return FALSE;
    }

    private function genTA827() {
        $segment01 = '01'
                . $this->getHeader()
                . $this->getReferenceNr()
                . $this->getDebitAccount()
                . $this->getPaymentAmount()
                . '              '; // Reserve (Seite 22)
        return $segment01;
    }

    private function getHeader() {
        $header = '';
        for ($i = 1; $i <= 51; $i++) {
            $header .= self::fillChar;
        }
        return $header;
    }

    public function setDtaId($dtaId) {
        if (!(strlen($dtaId) == 5))
            throw new Exception("Übergebene DTA-ID hat nicht 5 stellen!");
        else
            $this->dtaId = $dtaId;
    }

    private function getDtaId() {
        if ($this->dtaId == NULL)
            throw new Exception("DTA-ID nicht gesetzt!");
        else
            return $this->dtaId;
    }

    private function getTransactionId() {
        return '12345678912';
    }

    private function getReferenceNr() {
        $result = $this->getDtaId() . $this->getTransactionId();
    }

    public function setDebitAccount($debitAccount) {
        if (strlen($debitAccount) > 24)
            throw new Exeption("Übergebenes zu belastendes Konto zu lang!");
        else
            $this->debitAccount = str_pad($debitAccount, 24, self::fillChar);
    }

    private function getDebitAccount() {
        if ($this->debitAccount == NULL)
            throw new Exception("Zu belastendes Konto nicht gesetzt!");
        else {
            if (strlen($this->debitAccount) != 24)
                throw new Exception("Gesetztes zu belastendes Konto hat ungültige Länge!");
            else
                return $this->debitAccount;
        }
    }

    public function setPaymentAmount($amount, $currencyCode, $valuta = NULL) {
        $paymentAmount = '';

        // Überprüfen des Valuta
        if ($valuta == NULL)
            $valuta = '      ';
        else {
            if ((!is_numeric($valuta)) ||  (!(strlen($valuta) == 6)))
                throw new Exception("Valuta muss ein Datum im Format JJMMTT sein!");
        }

        // Überprüfen des Betrages
        if ((!is_float($amount)) || (!is_integer($amount)))
            throw new Exeption("Der übergebene Betrag muss Eine Zahl sein!");
        else
            $amount = str_pad(number_format($amount, 2, ',', ''), 12, self::fillChar);

        // Überprüfen des Währungscodes
        if (!$this->isIsoCurrencyCode($currencyCode))
            throw new Exception("Übergebener ISO-Währungscode nicht bekannt!");

        $paymentAmount = $valuta . $amount . $currencyCode;
        if (strlen($paymentAmount) != (6 + 3 + 12 ))
            throw new Exception("Zu setzender Vergütungsbetrag hat ungültige Länge!");
        else
            $this->paymentAmount = $paymentAmount;
    }

    private function getPaymentAmount() {
        if ($this->paymentAmount == NULL)
            throw new Exception("Vergütungsbetrag nicht gesetzt!");
        else {
            if (strlen($this->paymentAmount) != (6 + 3 + 12))
                throw new Exception("Gesetzter Vergütungsbetrag hat ungültige Länge!");
            else
                return $this->paymentAmount;
        }
    }

}

?>
