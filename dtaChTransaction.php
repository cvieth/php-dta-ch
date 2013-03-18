<?php

/**
 * Klasse zum Erzeugen von DTA Transaktionen im schweizer Six Interbank Clearing
 * Format.
 *
 * @author Christoph Vieth <cvieth@coreweb.de>
 */
class dtaChTransaction {

    const fillChar = ' ';

    private $dtaId = NULL;
    private $debitAccount = NULL;
    private $paymentAmount = NULL;

    private function genTA827() {
        $segment01 = '01'
                . $this->getHeader()
                . $this->getReferenceNr()
                . $this->getDebitAccount();
    }

    private function getHeader() {
        $header = '';
        for ($i = 1; $i <= 51; $i++) {
            $header .= self::fillChar;
        }
        return $header;
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
        
    }

}

?>
