<?php
/**
 * Created by PhpStorm.
 * User: pzhuchkov
 * Date: 25.08.17
 * Time: 22:41
 */

namespace Push\StatBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Push\StatBundle\Entity\Book;

/**
 * Class BookRepository
 *
 * @package Push\StatBundle\Repository
 */
class BookRepository extends EntityRepository
{
    /**
     * create
     *
     * @param string $name book name
     *
     * @return Book
     */
    public function create($name)
    {
        $book = $this->findOneBy(['name' => $name]);
        if (is_null($book)) {
            $book = new Book();
            $book->setName($name);
            $this->getEntityManager()->persist($book);
            $this->getEntityManager()->flush();
        }

        return $book;
    }

    /**
     * remove
     *
     * @param string $name book name
     *
     * @return bool
     */
    public function remove($name)
    {
        $book = $this->findOneBy(['name' => $name]);

        if (!is_null($book)) {
            $this->getEntityManager()->remove($book);
            $this->getEntityManager()->flush();

            return true;
        }

        return false;
    }
}