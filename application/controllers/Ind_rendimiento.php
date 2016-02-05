<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ind_rendimiento extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Ind_rendimientoModel');
        $this->load->helper('url');
    }

    function index()
    {
        $data = array();
        $data['periodos'] = $this->Ind_rendimientoModel->getAllPeriods(); 
        $data['cursos'] = $this->Ind_rendimientoModel->getAllClassroom(); 
        $data['trimestres'] = $this->Ind_rendimientoModel->getAllTrimestres(); 
        
        $this->load->view('/ind_rendimiento/index.php', $data);
    }

    public function getFilterIndRendimiento()
    {
        $periodo=$_POST['periodo_lectivo'];
        $aula=$_POST['curso'];
        $trimestre=$_POST['trimestre'];

        $data['totalRegistros'] = $this->Ind_rendimientoModel->totalRegistros($periodo, $aula, $trimestre);
        $materias = $this->Ind_rendimientoModel->getAllMaterias($aula);
        
        $total = 0;
        $row = $this->getValuesByMaterias($data['totalRegistros'], $materias, $total); 
        $result = array(
            'Materias' => array(),
            'Criticos' => array(),
            'Riesgo' => array()
            );
        
        $this->getPercentByMaterias($row, $total, $result, $materias);
        echo json_encode($result);
    }

/*
* Crea un array multidimensional que contiene la cantidad de notas Critica y en Riesgo 
* de cada materia.
*
* MATERIA1  => CRITICOS = x
*           => RIESGO = y
* MATERIA2  => CRITICOS = x
*           => RIESGO = y
*/
    public function getValuesByMaterias($totalRegistros, $materias, &$total)
    {        
        $cantidadrendimiento = array();
        foreach ($materias as $key => $value) 
            $cantidadrendimiento[$value['name']] = array('Criticos' => 0, 'Riesgo' => 0);

        foreach ($totalRegistros as $key => $value) 
        {
            $total++;
            $aux = $value['mat_descripcion']; 
            if($value['not_nota'] < 4) 
                    $cantidadrendimiento[$aux]['Criticos'] = $cantidadrendimiento[$aux]['Criticos'] + 1;
            if (($value['not_nota'] >= 4) && ($value['not_nota'] <= 5) )
                    $cantidadrendimiento[$aux]['Riesgo'] = $cantidadrendimiento[$aux]['Riesgo'] + 1;
        } 
        return $cantidadrendimiento;
    }
/*
* Cargo el arreglo $result, con los porcentajes a graficar junto con los nombres
* de las materias. Este array es devuelto con JSON para facilitar la lectura
* en el index.php (encargado de graficar)
*/
    public function getPercentByMaterias($row, $total, &$result, $materias)
    {
        foreach ($row as $key => $value) {
            array_push($result['Criticos'], round(($value['Criticos'] * 100) / $total, 2));
            array_push($result['Riesgo'], round(($value['Riesgo'] * 100) / $total, 2));
        }        
        foreach ($materias as $key => $value) 
            array_push($result['Materias'], $value['name']);        
    }
}
