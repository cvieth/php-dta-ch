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
        if (strlen($dtaId) == 5)
            $this->dtaId = $dtaId;
        else
            throw new Exception("Übergebene DTA-ID hat nicht 5 stellen!");
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

    private function getDebitAccount() {
        if ($this->debitAccount == NULL)
            throw new Exception("Zu belastendes Konto nicht gesetzt!");
        else
            return str_pad($this->debitAccount, 24, self::fillChar);
    }

    private function getPaymentAmount() {
        if ($this->paymentAmount == NULL)
            throw new Exception("Vergütungsbetrag nicht gesetzt!");
        else
        if (strlen($this->paymentAmount) != (6 + 3 + 12))
            throw new Exception("Gesetzter Vergütungsbetrag hat ungültige Länge!");
        else
            return $this->dtaId;
    }

}

?>
