<?php
/**
 * Copyright (c) 2016, whatwedo GmbH
 * All rights reserved
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
 * NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
 * WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace whatwedo\SearchBundle\Repository;

use whatwedo\CoreBundle\Repository\EntityRepository;
use whatwedo\SearchBundle\Entity\Index;

/**
 * Class IndexRepository
 * @package whatwedo\SearchBundle\Repository
 */
class IndexRepository extends EntityRepository
{

    /**
     * @param $query
     * @param string $entity
     * @param string $field
     * @return array
     */
    public function search($query, $entity = null, $field = null)
    {
        // Build query
        $qb = $this->createQueryBuilder('i')
            ->select('i.foreignId')
            ->where("MATCH_AGAINST(i.content, :query) > 0")
            ->orWhere('i.content LIKE :queryWildcard')
            ->groupBy('i.foreignId')
            ->setParameter('query', $query)
            ->setParameter('queryWildcard', '%'.$query.'%');
        if ($entity != null) {
            $qb->andWhere('i.model = :entity')
                ->setParameter('entity', $entity);
        }
        if ($field != null) {
            $qb->andWhere('i.field = :fieldName')
                ->setParameter('fieldName', $field);
        };

        // Get query result
        $result = $qb->getQuery()->getScalarResult();

        // Get ID's
        $ids = [];
        foreach ($result as $row) {
            $ids[] = $row['foreignId'];
        }
        return $ids;
    }

    /**
     * @param $entity
     * @param $field
     * @param $foreignId
     * @return Index|null
     */
    public function findExisting($entity, $field, $foreignId)
    {
        return $this->createQueryBuilder('i')
            ->where('i.model = :entity')
            ->andWhere('i.field = :field')
            ->andWhere('i.foreignId = :foreignId')
            ->setParameter('entity', $entity)
            ->setParameter('field', $field)
            ->setParameter('foreignId', $foreignId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
