<?php

namespace KV;

/**
 * Issue model
 */
class Issue
{
    /**
     * Issue ID
     * @var int
     */
    public $id;

    /**
     * Issue number
     * @var int
     */
    public $number;

    /**
     * Issue publication date
     * @var \DateTime
     */
    public $date;

    /**
     * Publication year
     * @var int
     */
    public $year;

    /**
     * Issue file hash
     * @var string
     */
    public $file;


    public function __construct()
    {
        if (is_string($this->date)) {
            $this->date = new \DateTime($this->date);
            $this->year = $this->date->format('Y');
            $this->month = $this->date->format('n');
        }
    }

    /**
     * Get full text description of issue
     *
     * Example: Газета «Качканарское время», №22 от 18 августа 2016
     *
     * @return string description
     */
    public function getDescription()
    {
        return 'Газета «Качканарское время», № '.$this->number.' от '.$this->prettyDate();
    }

    /**
     * Returns pretty full date
     *
     * Example: 18 августа 2016
     *
     * @return string Date string
     */
    public function prettyDate()
    {
        $string = $this->date->format('j');

        $months = array(
            'января', 'февраля', 'марта', 'апреля', 'мая', 'июня',
            'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'
        );

        $string .= ' ' . $months[$this->date->format('n')-1];

        if ($this->date->format('Y') != date('Y')) {
            $string .= ' ' . $this->date->format('Y');
        }

        return $string;
    }
}
