<?php

/**
 * Klasse zum Erzeugen von DTA Transaktionen im schweizer Six Interbank Clearing
 * Format.
 *
 * @author Christoph Vieth <cvieth@coreweb.de>
 */
class dtaChTransaction {

    const TA827 = 827;
    const TA890 = 890;

    /**
     * Füllzeichen
     * @var char 
     */
    private $fillChar = ' ';

    /**
     * Typ des Records
     * @var string
     */
    private $type = NULL;

    /**
     * DTA-ID
     * @var string
     */
    private $dtaId = NULL;

    /**
     * Zu belastendes Konto
     * @var string
     */
    private $debitAccount = NULL;

    /**
     * verhütungsbetrag
     * @var string
     */
    private $paymentAmount = NULL;

    /**
     * Eingabe-Sequenznummer
     * @var int
     */
    private $inputSequenceNr = NULL;

    /**
     * Bankenclearing-Nr. der bank des Auftraggebers
     * @var int
     */
    private $clientClearingNr = NULL;

    /**
     *  Erstellungsdatum
     */
    private $creationDate = NULL;

    public function __construct($transactionType) {
        $avaliableTypes = array(self::TA827);
        if (!in_array($transactionType, $avaliableTypes))
            throw new Exception("Transaktionstyp nicht bekannt oder nicht implementiert!");
        else
            $this->type = $transactionType;
    }

    public function test() {
        var_dump($this->genTA827());
    }

    public function toString() {
        switch ($this->type) {
            case self::TA827:
                $record = $this->genTA827();
                break;

            default:
                throw new Exception("Transaktionstyp nicht nicht implementiert!");
                break;
        }
        $string = '';
        while ($segment = array_pop($record)) {
            $string = $segment . "\n" . $string;
        }
        return $string;
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

    /**
     * Erzeugt eine TA827 Transaktion
     * 
     * @return array
     */
    private function genTA827() {
        $record = array();
        // Segment 01
        $segment01 = '01'
                . $this->getHeader()
                . $this->getReferenceNr()
                . $this->getDebitAccount()
                . $this->getPaymentAmount()
                . $this->getReserve(14);
        array_push($record, $segment01);

        // Segment 02
        $segment02 = '02'
                . $this->getClient()
                . $this->getReserve(30);
        array_push($record, $segment02);

        // segment 03
        $segment03 = '03'
                . $this->getRecipient();
        array_push($record, $segment03);

        // segment 04
        $segment04 = '04'
                . $this->getPaymentReason()
                . $this->getReserve(14);
        array_push($record, $segment04);

        // segment 05
        $segment05 = '05'
                . $this->getEndRecipient();
        array_push($record, $segment05);

        return $record;
    }

    private function getReserve($length) {
        $reserve = '';
        for ($i = 1; $i <= $length; $i++) {
            $reserve .= $this->fillChar;
        }
        return $reserve;
    }

    private function getHeader() {
        $header = $this->getProcessingDay()
                . $this->getRecipientClearingNr()
                . $this->getOutputSequenceNr()
                . $this->getCreationDate()
                . $this->getClientClearingNr()
                . $this->getDtaId()
                . $this->getInputSequenceNr()
                . $this->getTransactionType()
                . $this->getPaymentType()
                . $this->getProcessingFlag();
        return $header;
    }

// Start der Header Funktionen
    private function getProcessingDay() {
        return $this->getReserve(6);
    }

    private function getRecipientClearingNr() {
        return $this->getReserve(12);
    }

    private function getOutputSequenceNr() {
        return '00000';
    }

    public function setCreationDate($creationDate) {
        if ((!is_numeric($creationDate)) && (!(strlen($creationDate) == 6)))
            throw new Exception("Valuta muss ein Datum im Format JJMMTT sein!");
        else
            $this->creationDate = $creationDate;
    }

    private function getCreationDate() {
        if ($this->creationDate == NULL)
            throw new Exception("Erstellungsdatum nicht gesetzt!");
        else
            return $this->creationDate;
    }

    public function setClientClearingNr($clearingNr) {
        if (!is_integer($clearingNr))
            throw new Exception("Übergebene Clearing-Nr der Bank des Auftraggebers ist ungültig!");
        else
            $this->clientClearingNr = $clearingNr;
    }

    private function getClientClearingNr() {
        if ($this->type == self::TA890)
            return $this->getReserve(7);
        elseif ($this->clientClearingNr == NULL)
            throw new Exception("Clearing-Nr der Bank des Auftraggebers nicht gesetzt!");
        else
            return str_pad($this->clientClearingNr, 7, $this->fillChar);
    }

    public function setInputSequenceNr($sequenceNr) {
        if (!is_integer($sequenceNr))
            throw new Exception("Übergebene Eingabe-Sequenznummer ist ungültig!");
        else
            $this->inputSequenceNr = $sequenceNr;
    }

    private function getInputSequenceNr() {
        if ($this->inputSequenceNr == NULL)
            throw new Exception("Eingabe-Sequenznummer nicht gesetzt!");
        else
            return str_pad($this->inputSequenceNr, 5, '0', STR_PAD_LEFT);
    }

    private function getTransactionType() {
        return $this->type;
    }

    private function getPaymentType() {
        return '0';
    }

    private function getProcessingFlag() {
        return '0';
    }

// Ende der Header Funktionen    

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
        list($hash) = str_split(strtoupper(hash('md5', $this->dtaId)), 11);
        return $hash;
    }

    private function getReferenceNr() {
        return $this->getDtaId() . $this->getTransactionId();
    }

    public function setDebitAccount($debitAccount) {
        if (strlen($debitAccount) > 24)
            throw new Exeption("Übergebenes zu belastendes Konto zu lang!");
        else
            $this->debitAccount = str_pad($debitAccount, 24, $this->fillChar);
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
        if (!((is_float($amount)) || (is_integer($amount))))
            throw new Exception("Der übergebene Betrag muss Eine Zahl sein!");
        else
            $amount = str_pad(number_format($amount, 2, ',', ''), 12, $this->fillChar);

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

    private function getClient() {
        return $this->getReserve(24)
                . $this->getReserve(24)
                . $this->getReserve(24)
                . $this->getReserve(24);
    }

    private function getRecipient() {
        return $this->getReserve(30)
                . $this->getReserve(24)
                . $this->getReserve(24)
                . $this->getReserve(24)
                . $this->getReserve(24);
    }

    private function getPaymentReason() {
        return $this->getReserve(28)
                . $this->getReserve(28)
                . $this->getReserve(28)
                . $this->getReserve(28);
    }

    private function getEndRecipient() {
        return $this->getReserve(30)
                . $this->getReserve(24)
                . $this->getReserve(24)
                . $this->getReserve(24)
                . $this->getReserve(24);
    }

}

?>
