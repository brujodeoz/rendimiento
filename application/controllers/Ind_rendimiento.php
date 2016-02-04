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
            'Criticos' => array(),
            'Riesgo' => array()
            );
        $this->getPercentByMaterias($row, $total, $result);
        echo json_encode($result);
    }

    public function getValuesByMaterias(&$totalRegistros, &$id, &$total)
    {
        $row = array(
                'MATEMATICA' => array('Criticos' => 0, 'Riesgo' => 0),
                'LENGUA' => array('Criticos' => 0, 'Riesgo' => 0),
                'CIENCIAS NATURALES' => array('Criticos' => 0, 'Riesgo' => 0),
                'CIENCIAS SOCIALES' => array('Criticos' => 0, 'Riesgo' => 0)
            );

        foreach ($totalRegistros as $key => $value) 
        {
            $total++;
            $aux = $value['matplan_ciclo'];            
            if($value['not_nota'] < 4)                 
                    $row[$aux]['Criticos'] = $row[$aux]['Criticos'] + 1;                
            if (($value['not_nota'] >= 4) && ($value['not_nota'] <= 5) )
                    $row[$aux]['Riesgo'] = $row[$aux]['Riesgo'] + 1;
        }    
        return $row;
    }

    public function getPercentByMaterias($row, $total, &$result)
    {
        foreach ($row as $key => $value) {
            array_push($result['Criticos'], round(($value['Criticos'] * 100) / $total, 2));
            array_push($result['Riesgo'], round(($value['Riesgo'] * 100) / $total, 2));
        }
    }
}
