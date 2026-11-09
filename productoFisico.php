<?php
require_once 'producto.php';
    class ProductoFisico extends Producto    
{
        public function __construct(string $SKU, string $nombre,float $precio, protected string $tipo, protected float $peso )
        {
            if ($precio < 0) {
                throw new InvalidArgumentException('Precio unitario inválido');
            }
            parent::__construct($SKU, $nombre, $precio);
        }
        #[Override]
        public function __toString(): string
        {
            return parent::__toString() . "// Tipo : $this->tipo // Peso : $this->peso )";
        }
        public function getTipo(): string{
            return $this->tipo;
        }
        public function getPeso(): float{
            return $this->peso;
        }
        public function setTipo(string $tipo): void {
            $this->tipo = trim($tipo);
        }
        public function setPeso(float $peso): void {
        if ($peso < 0){
            throw new InvalidArgumentException('Peso inválido');
            }
            $this->peso = $peso;
        } 
}