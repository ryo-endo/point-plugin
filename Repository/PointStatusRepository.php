<?php


namespace Plugin\Point\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Plugin\Point\Entity\PointStatus;

/**
 * Class PointStatusRepository
 * @package Plugin\Point\Repository
 */
class PointStatusRepository extends EntityRepository
{
    /**
     * 仮ステータスの受注ID一覧を返却する
     * @param $customer_id
     * @return array
     */
    public function selectOrderIdsWithUnfixedByCustomer($customer_id)
    {
        // 会員IDをもとに仮付与ポイントを計算
        $qb = $this->createQueryBuilder('p')
            ->select('p.order_id')
            ->andWhere('p.customer_id = :customer_id')
            ->andWhere('p.status = :status')
            ->setParameter('customer_id', $customer_id)
            ->setParameter('status', 0);

        $result = $qb->getQuery()->getScalarResult();

        $orderIds = array();
        foreach ($result as $item) {
            $orderIds[] = $item['order_id'];
        }

        return $orderIds;
    }

    /**
     * 確定ステータスの受注ID一覧を返却する
     * @param $customer_id
     * @return array
     */
    public function selectOrderIdsWithFixedByCustomer($customer_id)
    {
        // 会員IDをもとに仮付与ポイントを計算
        $qb = $this->createQueryBuilder('p')
            ->select('p.order_id')
            ->andWhere('p.customer_id = :customer_id')
            ->andWhere('p.status = :status')
            ->setParameter('customer_id', $customer_id)
            ->setParameter('status', 1);

        $result = $qb->getQuery()->getScalarResult();

        $orderIds = array();
        foreach ($result as $item) {
            $orderIds[] = $item['order_id'];
        }

        return $orderIds;
    }

    /**
     * 受注情報をもとに、ポイントが確定かどうか判定
     * @param $order
     * @return bool|null
     */
    public function isFixedStatus($order)
    {
        // 必要エンティティ判定
        if (empty($order)) {
            return false;
        }

        try {
            // 受注をもとに仮付与ポイントを計算
            $qb = $this->createQueryBuilder('p');
            $qb->where('p.order_id = :order_id')
                ->setParameter('order_id', $order->getId())
                ->orderBy('p.plg_point_status_id', 'desc')
                ->setMaxResults(1);

            $result = $qb->getQuery()->getResult();
            if (count($result) < 1) {
                return false;
            }

            return ($result[0]->getStatus() == 1);
        } catch (NoResultException $e) {
            return null;
        }
    }
}