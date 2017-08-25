<?php
/**
 * Created by PhpStorm.
 * User: pzhuchkov
 * Date: 25.08.17
 * Time: 16:19
 */

namespace Push\StatBundle\Entity;

/**
 * Class WordStat
 *
 * @package Push\StatBundle\Entity
 */
class WordStat
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $word;

    /**
     * @var integer
     */
    protected $val;

    /**
     * @var integer
     */
    protected $bookId;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getWord()
    {
        return $this->word;
    }

    /**
     * @param string $word
     */
    public function setWord($word)
    {
        $this->word = $word;
    }

    /**
     * @return int
     */
    public function getVal()
    {
        return $this->val;
    }

    /**
     * @param int $val
     */
    public function setVal($val)
    {
        $this->val = $val;
    }

    /**
     * @return integer
     */
    public function getBookId()
    {
        return $this->bookId;
    }

    /**
     * @param integer $book
     */
    public function setBook($book)
    {
        $this->bookId = $book;
    }
}