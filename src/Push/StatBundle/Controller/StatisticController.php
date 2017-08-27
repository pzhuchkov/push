<?php

namespace Push\StatBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations as REST;
use FOS\RestBundle\Controller\Annotations\QueryParam;

/**
 * Class StatisticController
 *
 * @REST\NamePrefix("v1_statistics_")
 * @package Push\StatBundle\Controller
 */
class StatisticController extends Controller
{
    /**
     * getC
     *
     * @return RedisAdapter
     */
    protected function getC()
    {
        return $this->container->get('cache.app');
    }

    /**
     * Получение кол-ва книг в базе
     *
     * @return JsonResponse
     *
     * @REST\Get("/statistic/books/count")
     * @REST\View(serializerGroups={"Default"})
     *
     * @ApiDoc(
     *     description="Получение кол-ва книг в базе",
     *     section="Books",
     *     statusCodes={
     *          200="OK",
     *          500="Ошибка сервера",
     *     }
     * )
     */
    public function getBooksCountAction()
    {
        $result = $this->getC()->getItem(md5(__METHOD__));

        if (!$result->isHit()) {
            $qb = $this
                ->getDoctrine()
                ->getRepository('PushStatBundle:Book')
                ->createQueryBuilder('book');

            $response = [
                'Result' => $qb
                    ->select($qb->expr()->count('book.id'))
                    ->getQuery()
                    ->getSingleScalarResult(),
            ];

            $result->set(json_encode($response));
            $this->getC()->save($result);
        }

        return new JsonResponse(json_decode($result->get()));
    }

    /**
     * Получение списка книг
     *
     * @param integer $offset offset
     * @param integer $limit  limit
     *
     * @return JsonResponse
     * @REST\Get("/statistic/books")
     * @REST\View(serializerGroups={"Default"})
     *
     * @QueryParam(name="offset", description="Offset")
     * @QueryParam(name="limit", description="Limit")
     *
     * @ApiDoc(
     *     description="Получение списка книг",
     *     section="Books",
     *     statusCodes={
     *          200="OK",
     *          500="Ошибка сервера",
     *     }
     * )
     */
    public function getBooksAction($offset = 0, $limit = 10)
    {
        $result = $this->getC()->getItem(md5(__METHOD__));

        if (!$result->isHit()) {
            $qb = $this
                ->getDoctrine()
                ->getRepository('PushStatBundle:Book')
                ->createQueryBuilder('book');

            $response = [
                'Result' => $qb
                    ->select('book.name')
                    ->setMaxResults($limit)
                    ->setFirstResult($offset)
                    ->getQuery()
                    ->getArrayResult(),
            ];

            $result->set(json_encode($response));
            $this->getC()->save($result);
        }

        return new JsonResponse(json_decode($result->get()));
    }

    /**
     * Получение кол-ва слов во всех книгах
     *
     * @return JsonResponse
     * @REST\Get("/statistic/word/count")
     * @REST\View(serializerGroups={"Default"})
     *
     * @ApiDoc(
     *     description="Получение кол-ва слов во всех книгах",
     *     section="Stat",
     *     statusCodes={
     *          200="OK",
     *          500="Ошибка сервера",
     *     }
     * )
     */
    public function getBooksWordCountAction()
    {
        $result = $this->getC()->getItem(md5(__METHOD__));

        if (!$result->isHit()) {
            $qb = $this
                ->getDoctrine()
                ->getRepository('PushStatBundle:WordStat')
                ->createQueryBuilder('stat');

            $response = [
                'Result' => (int)$qb
                    ->select('SUM(stat.val)')
                    ->where($qb->expr()->eq('stat.bookId', 0))
                    ->getQuery()
                    ->getSingleScalarResult(),
            ];

            $result->set(json_encode($response));
            $this->getC()->save($result);
        }

        return new JsonResponse(json_decode($result->get()));
    }

    /**
     * Получение кол-ва слов в книге
     *
     * @param string $book book name
     *
     * @return JsonResponse
     *
     * @REST\Get("/statistic/word/{book}/count")
     * @REST\View(serializerGroups={"Default"})
     *
     * @ApiDoc(
     *     description="Получение кол-ва слов в книге",
     *     section="Stat",
     *     statusCodes={
     *          200="OK",
     *          500="Ошибка сервера",
     *     }
     * )
     */
    public function getBookWordCountAction($book)
    {
        $result = $this->getC()->getItem(md5(__METHOD__));

        if (!$result->isHit()) {
            $qb = $this
                ->getDoctrine()
                ->getRepository('PushStatBundle:WordStat')
                ->createQueryBuilder('stat');

            $qb2 = $this
                ->getDoctrine()
                ->getRepository('PushStatBundle:Book')
                ->createQueryBuilder('book');

            $response = [
                'Result' => $qb
                    ->select($qb->expr()->count('stat.id'))
                    ->where(
                        $qb->expr()->in(
                            'stat.bookId',
                            $qb2
                                ->select('book.id')
                                ->where(
                                    $qb2->expr()->eq('book.name', ':name')
                                )
                                ->getDQL()
                        )
                    )
                    ->setParameter('name', $book)
                    ->getQuery()
                    ->getSingleScalarResult(),
            ];

            $result->set(json_encode($response));
            $this->getC()->save($result);
        }

        return new JsonResponse(json_decode($result->get()));
    }

    /**
     * Получение частоты вхождения слова, во всех книгах
     *
     * @param string $word word
     *
     * @return JsonResponse
     *
     * @REST\Get("/statistic/word/{word}/frequency/count")
     * @REST\View(serializerGroups={"Default"})
     *
     * @ApiDoc(
     *     description="Получение кол-ва слов во всех книгах",
     *     section="Stat",
     *     statusCodes={
     *          200="OK",
     *          500="Ошибка сервера",
     *     }
     * )
     */
    public function getBooksFrequencyCountAction($word)
    {
        $result = $this->getC()->getItem(md5(__METHOD__));

        if (!$result->isHit()) {
            $qb = $this
                ->getDoctrine()
                ->getRepository('PushStatBundle:WordStat')
                ->createQueryBuilder('stat');

            $response = [
                'Result' => $qb
                    ->select('stat.val')
                    ->where($qb->expr()->eq('stat.bookId', 0))
                    ->andWhere($qb->expr()->eq('stat.word', ':word'))
                    ->setParameter('word', $word)
                    ->getQuery()
                    ->getSingleScalarResult(),
            ];

            $result->set(json_encode($response));
            $this->getC()->save($result);
        }

        return new JsonResponse(json_decode($result->get()));
    }

    /**
     * Получение частоты вхождения слова, в книге
     *
     * @param string $book book
     * @param string $word word
     *
     * @return JsonResponse
     * @REST\Get("/statistic/word/{book}/{word}/frequency/count")
     * @REST\View(serializerGroups={"Default"})
     *
     * @ApiDoc(
     *     description="Получение частоты вхождения слова, в книге",
     *     section="Stat",
     *     statusCodes={
     *          200="OK",
     *          500="Ошибка сервера",
     *     }
     * )
     */
    public function getBookFrequencyCountAction($book, $word)
    {
        $result = $this->getC()->getItem(md5(__METHOD__));

        if (!$result->isHit()) {
            $qb = $this
                ->getDoctrine()
                ->getRepository('PushStatBundle:WordStat')
                ->createQueryBuilder('stat');

            $qb2 = $this
                ->getDoctrine()
                ->getRepository('PushStatBundle:Book')
                ->createQueryBuilder('book');

            $response = [
                'Result' => $qb
                    ->select('stat.val')
                    ->where(
                        $qb->expr()->in(
                            'stat.bookId',
                            $qb2
                                ->select('book.id')
                                ->where(
                                    $qb2->expr()->eq('book.name', ':book')
                                )
                                ->getDQL()
                        )
                    )
                    ->andWhere(
                        $qb->expr()->eq('stat.word', ':word')
                    )
                    ->setParameter('word', $word)
                    ->setParameter('book', $book)
                    ->getQuery()
                    ->getSingleScalarResult(),
            ];

            $result->set(json_encode($response));
            $this->getC()->save($result);
        }

        return new JsonResponse(json_decode($result->get()));
    }
}
