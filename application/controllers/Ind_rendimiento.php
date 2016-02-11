<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ind_rendimiento extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Ind_rendimientoModel');
        $this->load->helper('url');
    }

    function index($nivel = 1)
    {
        $data = array();
        if($nivel == 1) $data['titulo'] = "Nivel Primario";
        if($nivel == 2) $data['titulo'] = "Nivel Secundario";        
        $data['periodos'] = $this->Ind_rendimientoModel->getAllPeriods(); 
        $data['cursos'] = $this->Ind_rendimientoModel->getAllClassroom($nivel = 1); 
        $data['trimestres'] = $this->Ind_rendimientoModel->getAllTrimestres(); 
        
        $this->load->view('/ind_rendimiento/index.php', $data);
    }

    public function getFilterIndRendimiento()
    {
        $periodo=$_POST['periodo_lectivo'];
        $aula=$_POST['curso'];
        $trimestre=$_POST['trimestre'];
        //$nivel=$_POST['nivel'];

        $data['totalRegistros'] = $this->Ind_rendimientoModel->totalRegistros($periodo, $aula, $trimestre, $nivel = 1);
        $materias = $this->Ind_rendimientoModel->getAllMaterias($aula, $nivel = 1);
        $total = 0; 
        $row = $this->getValuesByMaterias($data['totalRegistros'], $materias, $total, $nivel = 1); 

        $result = array(
            'Materias' => array(),
            'Criticos' => array(),
            'Riesgo' => array()
            );
        
        $this->getPercentByMaterias($row, $total, $result);
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
    public function getValuesByMaterias($totalRegistros, $materias, &$total, $nivel)
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
        return $this->filtroMaterias($cantidadrendimiento, $nivel = 1);
    }
/*
* Cargo el arreglo $result, con los porcentajes a graficar junto con los nombres
* de las materias. Este array es devuelto con JSON para facilitar la lectura
* en el index.php (encargado de graficar)
*/
    public function getPercentByMaterias($row, $total, &$result)
    {
        foreach ($row as $key => $value) 
        {
            array_push($result['Criticos'], round(($value['Criticos'] * 100) / $total, 2));
            array_push($result['Riesgo'], round(($value['Riesgo'] * 100) / $total, 2));
            array_push($result['Materias'], $key);
        }    
    }

    private function artificio($row, &$i)
    {
        $tmpaux = array();
        foreach ($row as $key => $value) 
        {
            $tmpaux[$i]['materia'] = $key;
            $tmpaux[$i]['nota'] = $value['Criticos'] + $value['Riesgo'];
            $i++;
        }
        return $tmpaux;
    }
    private function ordenarvector($tmpaux, $i)
    {
        for ($k = 0; $k <= $i-1 ; $k++) 
        { 
            for($j = 0; $j < $i-1; $j++)
            {
                if($tmpaux[$j]['nota'] < $tmpaux[$j+1]['nota'])
                {
                    $aux1 = $tmpaux[$j]['materia'];
                    $aux2 = $tmpaux[$j]['nota'];
                    $tmpaux[$j]['materia'] = $tmpaux[$j+1]['materia'];
                    $tmpaux[$j]['nota'] = $tmpaux[$j+1]['nota'];
                    $tmpaux[$j+1]['materia'] = $aux1;
                    $tmpaux[$j+1]['nota'] = $aux2;
                }
            }
        }
        return $tmpaux;
    }    
/*
* Funcion encargada de filtar las materias que seran graficadas, en caso del nivel primario 
* no requiere ningun calculo (se especifican implicitamente en la documentacion de requermientos),
* no asi para el nivel secundario. Tambien se descartan las materias que no contienen valores (criticos/riesgo)
*/
    private function filtroMaterias($row, $nivel)
    {
        $aux = array();
        if($nivel == 2)
        {
            foreach ($row as $key => $value) 
                if($key == 'LENGUA' || $key == 'MATEMATICA' || $key == 'CIENCIAS NATURALES' || $key == 'CIENCIAS SOCIALES') 
                {
                    if(($value['Criticos']+$value['Riesgo']) > 0 )
                    {
                        $aux[$key]['Criticos'] = $value['Criticos'];
                        $aux[$key]['Riesgo'] = $value['Riesgo'];
                    }
                }
        } else if($nivel == 1) 
        {
            $i = 0;
            $vecNormal = $this->artificio($row, $i);
            $vecNormal = $this->ordenarvector($vecNormal, $i);
            $devolver = array();
            $k = 0;
            foreach ($vecNormal as $value) 
            {
                $devolver[$value['materia']] = array('Criticos' => 0, 'Riesgo' => 0);                
                if (++$k >= 5) break;
            }
            foreach ($devolver as $key => $value) 
            {
                $devolver[$key]['Criticos'] = $row[$key]['Criticos'];
                $devolver[$key]['Riesgo'] = $row[$key]['Riesgo'];
            }
        }
        return $devolver;
    }
}