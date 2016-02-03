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
        $porcentajes = $this->getPercentByMaterias($row, $total);
/*********************/
// Queria graficar directamente con $porcentajes, pero se me estaba complicando
// asi que, por el momento harcodie esta parte
        $hardcode = array('Criticos' => array(), 'Riesgo' => array());
            foreach ($porcentajes as $key => $value) {
                array_push($hardcode['Criticos'], $value['Criticos']);
                array_push($hardcode['Riesgo'], $value['Riesgo']);
            }
/*********************/
        echo json_encode($hardcode);
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

    public function getPercentByMaterias($row, $total)
    {
        $row['MATEMATICA']['Criticos'] = round(($row['MATEMATICA']['Criticos'] * 100) / $total, 2 );
        $row['MATEMATICA']['Riesgo'] = round(($row['MATEMATICA']['Riesgo'] * 100) / $total, 2 );
        $row['LENGUA']['Criticos'] = round(($row['LENGUA']['Criticos'] * 100) / $total, 2 );
        $row['LENGUA']['Riesgo'] = round(($row['LENGUA']['Riesgo'] * 100) / $total, 2 );
        $row['CIENCIAS NATURALES']['Criticos'] = round(($row['CIENCIAS NATURALES']['Criticos'] * 100) / $total, 2 );
        $row['CIENCIAS NATURALES']['Riesgo'] = round(($row['CIENCIAS NATURALES']['Riesgo'] * 100) / $total, 2 );
        $row['CIENCIAS SOCIALES']['Criticos'] = round(($row['CIENCIAS SOCIALES']['Criticos'] * 100) / $total, 2 );
        $row['CIENCIAS SOCIALES']['Riesgo'] = round(($row['CIENCIAS SOCIALES']['Riesgo'] * 100) / $total, 2 );
        return $row;
    }
}
