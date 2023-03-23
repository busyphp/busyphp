<?php

namespace BusyPHP\helper;

class ReflectionNamedType extends \ReflectionNamedType
{
    protected string $setName;
    
    protected bool   $setBuiltin;
    
    protected bool   $allowsNull;
    
    
    public function __construct(string $name, bool $builtin, bool $allowsNull = true)
    {
        $this->setName    = $name;
        $this->setBuiltin = $builtin;
        $this->allowsNull = $allowsNull;
    }
    
    
    public function isBuiltin()
    {
        return $this->setBuiltin;
    }
    
    
    public function getName()
    {
        return $this->setName;
    }
    
    
    public function allowsNull()
    {
        return $this->allowsNull;
    }
}