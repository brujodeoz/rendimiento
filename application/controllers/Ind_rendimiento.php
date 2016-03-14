<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ind_rendimiento extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Ind_rendimientoModel');
        $this->load->helper('url');
    }

    function index($nivel = 1, $establecimiento = 99)    
    {
        $data = array();
        $data['establecimiento'] = $establecimiento;
        $data['nivel'] = $nivel;
        $this->generaEtiquetas($data, $nivel, $establecimiento);
        $data['periodos'] = $this->Ind_rendimientoModel->getAllPeriods(); 
        $data['trimestres'] = $this->Ind_rendimientoModel->getAllTrimestres(); 
        
        $this->load->view('/ind_rendimiento/index.php', $data);
    }

    public function getFilterIndRendimiento()
    {
        $periodo=$_POST['periodo_lectivo'];
        $aula=$_POST['curso']; // realmente es un item
        $trimestre=$_POST['trimestre'];
        $nivel=$_POST['nivel']; 
        if($nivel == 3) // parche para reutilizar en rol de supervisor
            {
                $nivel = 1;
                $aula = "-";
            }
        $establecimiento=$_POST['establecimiento'];

        $data['totalRegistros'] = $this->Ind_rendimientoModel->totalRegistros($periodo, $aula, $trimestre, $nivel, $establecimiento);
        $materias = $this->Ind_rendimientoModel->getAllMaterias($aula, $nivel);
        $total = 0; 
        $RegValuesGraph = $this->getValuesByMaterias($data['totalRegistros'], $materias, $total, $nivel);

        $result = array(
            'Materias' => array(),
            'Criticos' => array(),
            'Riesgo' => array()
            );
        
        $this->getPercentByMaterias($RegValuesGraph, $total, $result);
        echo json_encode($result);
    }

    public function getValuesByMaterias($totalRegistros, $materias, &$total, $nivel)
    {        
/*
* Crea un array multidimensional que contiene la cantidad de notas Criticas, en Riesgo 
* y un Promedio de cada materia.
* Promedio se utiliza en caso del nivel secundario para mostrar los peores 5 rendimientos
* ID_MATERIA1   => CRITICOS = x
*               => RIESGO = y
*               => PROMEDIO = z
*               => MATERIA = NOMBRE_MATERIA
* 
*/
        $RendimientoMaterias = array();
        foreach ($materias as $key => $value) // Defino el arreglo multidimiensional
            $RendimientoMaterias[$value['id']] = array('Materia'=>"vacio", 'Criticos'=>0, 'Riesgo'=>0, 'Promedio'=>0);

        foreach ($totalRegistros as $key => $value) 
        {
            $total++;
            $mat_id = $value['mat_id']; 
            if($value['not_nota'] <= 5)
                    $RendimientoMaterias[$mat_id]['Materia'] = $value['mat_descripcion'];
            if($value['not_nota'] < 4) 
                    $RendimientoMaterias[$mat_id]['Criticos'] = $RendimientoMaterias[$mat_id]['Criticos'] + 1;
            if (($value['not_nota'] >= 4) && ($value['not_nota'] <= 5) )
                    $RendimientoMaterias[$mat_id]['Riesgo'] = $RendimientoMaterias[$mat_id]['Riesgo'] + 1;
        } 
        foreach ($RendimientoMaterias as $key => $value) 
        {
            $RendimientoMaterias[$key]['Promedio'] = ($value['Riesgo'] + $value['Criticos'])/2;
        }
        $temporal = $this->filtroMaterias($RendimientoMaterias, $nivel);
        return $temporal;
    }

    public function getPercentByMaterias($RegValuesGraph, $total, &$result)
/*
* Cargo el arreglo $result, con los porcentajes a graficar junto con los nombres
* de las materias.
*/    
    {
        foreach ($RegValuesGraph as $key => $value) 
        {
            if ($total <= 0 ) break;
            array_push($result['Criticos'], round(($value['Criticos'] * 100) / $total, 2));
            array_push($result['Riesgo'], round(($value['Riesgo'] * 100) / $total, 2));
            array_push($result['Materias'], $key);
        }    
    }


    private function filtroMaterias($RendimientoMaterias, $nivel)
    {
/*
* Funcion encargada de filtar las materias que seran graficadas, en caso del nivel primario 
* no requiere ningun calculo (se especifican implicitamente en la documentacion de requermientos),
* no asi para el nivel secundario. 
* Tambien se descartan las materias que no contienen valores (criticos/riesgo)
*/
        $aux = array();
        if($nivel == 1)
            $materiasporfiltrar = array('LENGUA', 'MATEMATICA', 'CIENCIAS NATURALES', 'CIENCIAS SOCIALES');
        else if($nivel == 2)
        {
            foreach ($RendimientoMaterias as $key => $value) 
            {
                $vecPromedio[$key] = $value['Promedio'];
                $materiasporfiltrar[$key] = $value['Materia'];
            }
            array_multisort($vecPromedio, SORT_DESC, $materiasporfiltrar);
            array_splice($materiasporfiltrar, 5);
        }

        foreach ($RendimientoMaterias as $key => $value) 
            if(in_array($value['Materia'], $materiasporfiltrar))
            {
                if(($value['Promedio']) > 0 ) // segun requerimientos, no muestra valores en cero
                {
                    $aux[$value['Materia']]['Criticos'] = $value['Criticos']?$value['Criticos']:0;
                    $aux[$value['Materia']]['Riesgo'] = $value['Riesgo']?$value['Riesgo']:0;
                }
            }
        return $aux;
    }

    public function generaEtiquetas(&$data, $nivel, $establecimiento)
    {
        switch ($nivel) {
            case 1:
                $data['titulo'] = "Director - Nivel Primario";
                $data['item'] = "Grado: ";
                $data['todos'] = "Todos los grados";
                $data['cursos'] = $this->Ind_rendimientoModel->getAllClassroom($nivel);  
                $nombre_establecimiento = $this->Ind_rendimientoModel->getNameEstablecimiento($establecimiento);                
                $data['nombre_establecimiento'] = "Establecimiento: ".$nombre_establecimiento[0]['name'];
                break;
            case 2:
                $data['titulo'] = "Director - Nivel Secundario";                
                $data['item'] = "Curso: ";
                $data['todos'] = "Todos los cursos";
                $data['cursos'] = $this->Ind_rendimientoModel->getAllClassroom($nivel);
                $nombre_establecimiento = $this->Ind_rendimientoModel->getNameEstablecimiento($establecimiento);                
                $data['nombre_establecimiento'] = "Establecimiento: ".$nombre_establecimiento[0]['name'];
                break;
            case 3:
                $data['titulo'] = "Supervisor - Nivel Primario";
                $data['item'] = "Establecimiento: ";
                $data['todos'] = "Elija un Establecimiento";
                $data['cursos'] = $this->Ind_rendimientoModel->getAllEstablecimientos();
                $data['nombre_establecimiento'] = "";
                break;
            
            default:
                # code...
                break;
        }
    }
}