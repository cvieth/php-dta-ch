<?php

/**
 * Description of dtaRecord
 *
 * @author Christoph Vieth <christoph.vieth@coreweb.de>
 */
class dtaChTransaction {

    private $type = '';
    private $senderClearingNr = '1234567';
    private $senderIdentification = 'ABC12';
    private $sequenceNr = 1;
    private $creationDate = '000000';
    // Liste der 
    private $fieldList = array();
    private $fieldLast = NULL;
    private $fieldsTA827 = array('20', '25', '32A', '50', '59', '70');

    const charFill = 0xFF;

    // Feldabgrenzungen
    const charSOH = 0x01;
    const charCR = 0x0D;
    const charLF = 0x25;
    const charPlus = 0x4E;
    const charDoppel = 0x7A;
    const charMinus = 0x60;
    const charETX = 0x03;

    public function __construct($seqNr, $type, $createDate, $ident) {
        $this->type = $type;
        $this->sequenceNr = $seqNr;
        $this->creationDate = $createDate;
        $this->senderIdentification = $ident;
    }

    private function addFieldEntry($field, $value) {
        if (!isset($this->fieldList[$field]))
            return $this->fieldList[$field] = $value;
        else
            throw new Exception("Feld schon gesetzt!");
    }

    private function genReferenceNr() {
        list($hash) = str_split(strtoupper(hash('md5', $this->senderIdentification . $this->sequenceNr)));
        return $this->senderIdentification . $hash;
    }

    public function setReferencNr() {
        return $this->addFieldEntry('20', $this->genReferenceNr());
    }

    public function setSenderAccount($account) {
        return $this->addFieldEntry('25', str_pad($account, 24, chr($this::charFill)));
    }

    public function setAmount($amount, $isoCode, $valuta = NULL) {
        if ($valuta = NULL)
            $valuta = str_pad('', 6, chr($this::charFill));
        $value = $valuta . chr(self::charCR) . chr(self::charLF)
                . $isoCode . chr(self::charCR) . chr(self::charLF)
                . $amount;
        return $this->addFieldEntry('32A', $value);
    }

    function createHeadSegment() {
        $header = '';

        // Gewünschter Verarbeitungstag
        if (($this->type == 826) || ($this->type == 827)) {
            $header .= date('ymd');
        } else {
            $header .= '000000';
        }

        // Bankenclearing-Nr. der Bank des Begünstigten
        $header .= str_pad('', 12, chr(self::charFill));

        // Ausgabesequenznummer
        $header .= str_pad('', 5, chr(self::charFill));

        // Erstellungsdatum
        $header .= $this->creationDate;

        // Bankenclearing-Nr. des Auftraggebers
        if ($this->type == 890)
            $header .= str_pad($this->senderClearingNr, 7, chr(self::charFill));
        else
            $header .= str_pad('', 7, chr(self::charFill));

        // Datenfile-Absender-Identifikation
        $header .= str_pad($this->senderIdentification, 5, chr(self::charFill));

        // Eingabe-Sequenznummer
        $header .= str_pad($this->sequenceNr, 5, '0', STR_PAD_LEFT);

        // Transaktionsart
        $header .= $this->type;

        // Zahlungsart 
        $header .= '0';

        // Bearbeitungs-Flag
        $header .= '0';

        if (strlen($header) != 51)
            throw new Exception('Länge des Record Header nicht korrekt!');
        else
            return $header;
    }

    private function createTextSegment() {
        switch ($this->type) {
            case 827:
                $textSegment = '';
                $field = TRUE;
                while ($field != FALSE) {
                    $field = array_shift($this->fieldsTA827);
                    if (!isset($this->fieldList[$field]))
                        throw new Exception('Feld "' . $field . '" nicht gesetzt!');
                    $textSegment .= $field . chr(self::charDoppel) . $this->fieldList[$field]
                            . chr(self::charCR) . chr(self::charLF) . chr(self::charDoppel);
                }
                return $textSegment;
                break;

            default:
                throw new Exception('Transaktionstyp nicht implementiert!');
                break;
        }
    }

    public function toString() {
        return chr(self::charSOH) . $this->createHeadSegment()
                . chr(self::charCR) . chr(self::charLF) . chr(self::charPlus)
                . $this->createTextSegment()
                . chr(self::charCR) . chr(self::charLF) . chr(self::charMinus)
                . chr(self::charETX);
    }

}

?>
