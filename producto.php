<?php 
    class Producto
    {
        public function __construct(protected string $SKU, protected string $nombre , protected float $precio)
        {
            if ($precio < 0) {
                $this->precio = 0;
                throw new InvalidArgumentException('Precio unitario inválido');
            }
        }

        public function __toString(): string
        {
            return "Producto (SKU : $this->SKU // Nombre : $this->nombre // Precio : $this->precio";
        }
        public function getSKU(): string 
        {
            return $this->SKU;
        }
        public function getNombre(): string {
            return $this->nombre;
        }
        public function getPrecio(): float {
            return $this->precio;
        }
        public function setSKU(string $SKU): void {
            $this->SKU = trim($SKU);
        }
        public function setPrecio(float $precio): void {
        if ($precio < 0) {
        throw new InvalidArgumentException('Precio unitario inválido');
        }
        $this->precio = $precio;
        }
        public function setNombre(string $nombre){
            $this->nombre = trim($nombre);
        }
    }