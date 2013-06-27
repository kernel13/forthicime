<?php 

namespace Forthicime\MedecinBundle\Entity;

use Doctrine\ORM\EntityRepository;

class MedecinRepository extends EntityRepository
{

    public function findAllOrderedByName()
    {
        return $this->getEntityManager()
            ->createQuery('SELECT p FROM AcmeStoreBundle:Product p ORDER BY p.name ASC')
            ->getResult();
    }
}