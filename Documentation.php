<?php
class POO {
    // polymorphisme
    // encapsulation
    // heritage
    // abstraction

    public $marque;

    // access modifiers: public - protected - private

    /**
     * public: accessible pour tous
     * protected: accessible par la class lui meme et le class fil
     */
    // method magic: 
    public function __construct($attribute1) {
        $this->attribute1 = $attribute1;
        echo 'work';    
    }


    public function sayHi() {
        echo "hey";
    }

    




}




$poo = new POO("name");


$poo->sayHi();

$pass = password_hash("adminPassword", PASSWORD_DEFAULT);

echo $pass;
?>
