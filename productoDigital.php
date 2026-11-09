<?php 
    require_once 'producto.php';

    class productoDigital extends Producto 
{

    public function __construct(string $SKU, string $nombre, float $precio, protected string $tipo , protected string $licencia )
    {
        return parent::__construct($SKU, $nombre, $precio);
    }

    #[Override]
    public function __toString(): string
    {
        return parent::__toString() . "// Tipo  : $this->tipo // Licencia : $this->licencia )";
    }
    public function getTipo(): string{
        return $this->tipo;
    }
    public function getLicencia(): string{
        return $this->licencia;
    }
    public function setLicencia(string $licencia): void{
        $this->licencia = trim($licencia);
    }
    public function setTipo(string $tipo): void{
        $this->tipo = trim($tipo);
    }
}
