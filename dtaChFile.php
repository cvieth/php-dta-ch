<?php

/**
 * @author Christoph Vieth <cvieth@coreweb.de>
 */
require_once(dirname(__FILE__) . '/dtaChTransaction.php');

class dtaChFile {

    private $transactions = array();
    private $transactionCounter = 0;
    private $creationDate;
    private $ident;
    private $clearingNr;
    public $currentTransaction = NULL;

    public function __construct($ident, $clearingNr) {
        $this->creationDate = date('ymd');
        $this->ident = $ident;
        $this->clearingNr = $clearingNr;
    }

    public function addTransaction($type) {
        $this->transactionCounter++;
        $seqNr = $this->transactionCounter;
        $this->transactions[$seqNr] = new dtaChTransaction($type);
        $this->transactions[$seqNr]->setInputSequenceNr($seqNr);
        $this->transactions[$seqNr]->setCreationDate($this->creationDate);
        $this->transactions[$seqNr]->setDtaId($this->ident);
        return $seqNr;
    }

    public function loadTransaction($seqNr) {
        return $this->transactions[$seqNr];
    }

    public function saveTransaction($seqNr, $transaction) {
        return $this->transactions[$seqNr] = $transaction;
    }

    private function createTotalRecord() {
        echo "### Erzeuge Totalrecord <br />/n";
        $sum = 0;
        foreach ($this->transactions as $transaction) {
            $sum += $transaction->getPaymentAmountNumeric();
        }
        echo "Summe: ".$sum." <br />/n";
        $id = $this->addTransaction(dtaChTransaction::TA890);
        $totalRecord = $this->loadTransaction($id);
        echo "Record ID: ".$id." <br />/n";
        $totalRecord->setTotalAmount($sum);
        $this->saveTransaction($id, $totalRecord);
        echo "Done! <br />/n";
    }

    public function toFile($filename) {
        $this->createTotalRecord();
        $fptr = fopen($filename, 'w+');
        if (!$fptr)
            throw new Exception('Kann Datei "' . $filename . '"nicht öffnen!');
        foreach ($this->transactions as $transaction) {
            //ceho "Writing Transaction: " . $transaction->getSeqNr() . "\n";
            fwrite($fptr, $transaction->toString());
        }
        fclose($fptr);
    }

    public function toString() {
        $this->createTotalRecord();
        $output = '';
        foreach ($this->transactions as $transaction) {
            //echo "Writing Transaction: " . $transaction->getSeqNr() . "\n";
            $output .= $transaction->toString();
        }
        return $output;
    }

}

?>
