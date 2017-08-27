<?php
/**
 * Created by PhpStorm.
 * User: pzhuchkov
 * Date: 25.08.17
 * Time: 21:18
 */

namespace Push\StatBundle\Service;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Push\StatBundle\Entity\Book;
use Symfony\Component\Filesystem\Filesystem;
use Monolog\Logger;

/**
 * Class WordManager
 *
 * @package Push\StatBundle\Service
 */
class WordManager
{
    /**
     * @var Registry
     */
    protected $doctrine;
    /**
     * @var Logger
     */
    protected $l;

    /**
     * @var int
     */
    protected $bulk = 5000;

    /**
     * @var array
     */
    protected $cache = [];

    /**
     * @var int
     */
    protected $counter = 0;

    /**
     * @var Book
     */
    protected $currentBook;

    /**
     * getM
     *
     * @return \Doctrine\Common\Persistence\ObjectManager|object
     */
    protected function getM()
    {
        return $this->doctrine->getManager();
    }

    /**
     * getC
     *
     * @return object
     */
    protected function getC()
    {
        return $this->doctrine->getConnection();
    }

    /**
     * @param int $bulk
     */
    public function setBulk($bulk)
    {
        $this->bulk = $bulk;
    }

    /**
     * WordManager constructor.
     *
     * @param Registry $doctrine
     * @param Logger   $logger
     */
    public function __construct(Registry $doctrine, Logger $logger)
    {
        $this->doctrine = $doctrine;
        $this->l = $logger;
    }

    /**
     * add
     *
     * @param string $name book name
     *
     * @return void
     */
    public function add($name)
    {
        if (!file_exists($name)) {
            throw new \RuntimeException(sprintf('File "%s" not found', $name));
        }

        $file = new \SplFileObject($name);

        $this->currentBook = $this
            ->getM()
            ->getRepository('PushStatBundle:Book')
            ->create($file->getFilename());

        while (!$file->eof()) {
            $this->parseLine(trim($file->fgets()));
        }

        $this->insert();

        $file = null;

        $this->currentBook = null;
    }

    /**
     * remove
     *
     * @param string $name name
     *
     * @return void
     */
    public function remove($name)
    {
        $this->currentBook = $this
            ->getM()
            ->getRepository('PushStatBundle:Book')
            ->create(basename($name));

        if (is_null($this->currentBook)) {
            throw new \RuntimeException(
                sprintf(
                    'Book "%s" not found',
                    $name
                )
            );
        }

        $qb = $this
            ->doctrine
            ->getRepository('PushStatBundle:WordStat')
            ->createQueryBuilder('wordStat');

        $words = $qb->select('wordStat.word', 'wordStat.val')
            ->where($qb->expr()->eq('wordStat.bookId', $this->currentBook->getId()))
            ->getQuery()
            ->getArrayResult();

        if (count($words)) {
            $sql = '';
            foreach ($words as $word) {

                $sql = sprintf(
                    'UPDATE word_stat SET val = val - %d WHERE word = \'%s\' AND book_id = 0',
                    $word['val'],
                    $word['word']
                );

                $this->l->debug('WORD_MANAGER', ['sql' => $sql]);

                $this->getC()->executeQuery($sql);
            }

            $qb = $this
                ->doctrine
                ->getRepository('PushStatBundle:WordStat')
                ->createQueryBuilder('wordStat');

            $qb
                ->delete()
                ->where($qb->expr()->eq('wordStat.bookId', ':book'))
                ->setParameter('book', $this->currentBook->getId())
                ->getQuery()
                ->getResult();
        } else {
            $this->l->debug(
                'WORD_MANAGER',
                [
                    'detail' => "words not found in db",
                ]
            );
        }


        $this->getM()->remove($this->currentBook);
        $this->getM()->flush();

        $file = null;

        $this->currentBook = null;
    }

    /**
     * Парсинг строки.
     *
     * По символьно проходим по строке, и считаем слова.
     *
     * Если буфер наполнен, сохраняем
     *
     * @param string $line line
     *
     * @return void
     */
    protected function parseLine($line)
    {
        if (strlen($line) === 0) {
            return;
        }
        $buff = $line[0];
        for ($i = 1; $i < strlen($line); $i++) {
            if ($line[$i] != ' ') {
                $buff .= $line[$i];
                continue;
            }

            $buff = $this->clean($buff);

            if (!array_key_exists($buff, $this->cache)) {
                $this->cache[$buff] = 0;
            }

            $this->cache[$buff]++;

            $this->counter++;

            $buff = '';

            if ($this->counter >= $this->bulk) {
                $this->insert();
            }
        }

        if (strlen($buff) > 0) {
            $buff = $this->clean($buff);
            if (!array_key_exists($buff, $this->cache)) {
                $this->cache[$buff] = 0;
            }

            $this->cache[$buff]++;

            $this->counter++;
        }
    }

    /**
     * clean
     *
     * @param string $string string
     *
     * @return mixed
     */
    protected function clean($string)
    {
        return preg_replace('/[^\w\s]+/u', '', $string);
    }

    /**
     * insert
     *
     * @return null
     */
    protected function insert()
    {
        $cnt = count($this->cache);
        if ($cnt == 0) {
            return null;
        }

        $sqlStat = [];

        $sqlStatBook = [];

        foreach ($this->cache as $name => $count) {
            $sqlStat[] = sprintf(
                '(%d, %d, \'%s\')',
                0,
                $count,
                $name
            );
            $sqlStatBook[] = sprintf(
                '(%d, %d, \'%s\')',
                $this->currentBook->getId(),
                $count,
                $name
            );
        }

        $this->getC()->executeQuery(
            sprintf(
                '%s %s %s',
                'INSERT INTO word_stat (book_id, val, word) VALUES ',
                implode(',', $sqlStat),
                'ON CONFLICT (word, book_id) DO UPDATE SET val = word_stat.val + EXCLUDED.val'
            )
        );
        $this->getC()->executeQuery(
            sprintf(
                '%s %s %s',
                'INSERT INTO word_stat (book_id, val, word) VALUES ',
                implode(',', $sqlStatBook),
                'ON CONFLICT (word, book_id) DO UPDATE SET val = word_stat.val + EXCLUDED.val'
            )
        );

        $this->counter = 0;
        $this->cache = [];
    }
}