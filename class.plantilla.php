<?php

  class Plantilla {

    // Atributos de la clase:
    protected $archivo;
    protected $tags = array();

    // Constructor de la clase:
    public function __construct($archivo) {
      $this->archivo = $archivo;
    }

    // Método para reemplazar los tags de la plantilla por valores:
    public function set($tag, $valor) {
      $this->tags[$tag] = $valor;
    }

    // Método para generar el texto procesado:
    public function render() {
      if (!file_exists($this->archivo)) {
        return "Error al cargar el archivo $this->archivo.";
      }
      $salida = file_get_contents($this->archivo);
      foreach ($this->tags as $tag => $valor) {
        $tag_a_reemplazar = "{@$tag}";
        $salida = str_replace($tag_a_reemplazar, $valor, $salida);
      }
      return $salida;
    }
  }

 ?>
