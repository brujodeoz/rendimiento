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
        print_r($data);
    }

    public function getValuesByCurse(&$totalRegistros, &$id)
    {
    }

    public function getPercentByCurse(&$result, $row)
    {
    }
}
