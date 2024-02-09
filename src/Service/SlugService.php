<?php


namespace App\Service;


use Symfony\Bundle\SecurityBundle\Security;

class SlugService
{

    private Security $security;

    public function __construct(Security $security) {

        $this->security = $security;
    }
    public function kebabcase(?string $string){
        // make string lowercase
        $string = strtolower($string);
        // replace spaces with hifens (-)
        $string = str_replace(" ","-",$string);

        return $string;
    }

    public function test(){
        dd($this->security->getUser()->getUserIdentifier());
    }
}