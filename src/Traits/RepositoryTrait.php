<?php

namespace App\Traits;

trait RepositoryTrait
{
    public function save($entity) 
    {
        $this->_em->persist($entity);
        $this->_em->flush();
    }

    public function persist($entity) 
    {
        $this->_em->persist($entity);
    }

    public function flush() 
    {
        $this->_em->flush();
    }
}