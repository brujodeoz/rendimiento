<?php
Class Ind_rendimientoModel extends CI_Model
{
	public function __construct()
	{
		$this->load->database();
	}

	private function ejecutasql($qsql)
	{
		$data = $this->db->query($qsql);
		$data = $data->result_array();
		return (count($data) < 1 ? array() : $data);
	}

	public function getAllMaterias($curso)
	{
		$sqlCurso = "and mat_curso = ".$curso;
		$qsql = <<<EOQ
select mat_id as id, mat_descripcion as name from materia 
where mat_descripcion in ('LENGUA','MATEMATICA','CIENCIAS NATURALES','CIENCIAS SOCIALES') 
and mat_nivel = 1
{$sqlCurso}
EOQ;
		return $this->ejecutasql($qsql);
	}
	
	public function totalRegistros($periodo, $aula, $trimestre)
	{		
		$sqlPeriodo = "";
		$sqlAula = "";
		$sqlTrimestre = "";

		if (!empty($periodo) && $periodo != '-') 
			$sqlPeriodo = " and i.ins_per_lectivo = ".$periodo;
		if (!empty($aula) && $aula != '-') 
			$sqlAula = " and m.mat_curso = ".$aula;
		if (!empty($trimestre) && $trimestre != '-') 
			$sqlTrimestre = " and te.tipexa_id = ".$trimestre;
		$sql = <<<EOQ
select n.not_id, i.ins_per_lectivo as periodo, n.not_nota, te.tipexa_descripcion, m.mat_descripcion, m.mat_curso
from nota n
 join tipo_examenes te on te.tipexa_id = n.tipexa_id 
  join docente_matplan dmp on dmp.docmatplan_id = n.docmatplan_id
   join materia_plan mp on mp.matplan_id = dmp.matplan_id
   join materia m on m.mat_id = mp.mat_id 
 join inscripcion i on i.ins_id = n.ins_id
where
 m.mat_descripcion IN ('LENGUA','MATEMATICA','CIENCIAS NATURALES','CIENCIAS SOCIALES') 
 and m.mat_nivel = 1
 {$sqlPeriodo}
 {$sqlAula}
 {$sqlTrimestre}
EOQ;
		return $this->ejecutasql($sql);
	}

	public function getAllPeriods()
	{
		$qsql = "select distinct(ins_per_lectivo) from inscripcion";
		return $this->ejecutasql($qsql);
	}

/*
* No existe una tabla puntual con los Grados, se utiliza la tabla materia 
* y se toma el campo mat_curso como key / value (id / name)
*/
	public function getAllClassroom()
	{		
		$qsql = "select cur_id as id, concat(cur_descripcion,' Grado') as name from cursos where cur_nivel=1";
		return $this->ejecutasql($qsql);
	}
/*
* En la tabla "tipo_examenes" tuve que modificar los caracteres "°" 
*/
	public function getAllTrimestres()
	{
		$qsql = "select tipexa_id as id, tipexa_descripcion as name from tipo_examenes where tipexa_descripcion in ('3° TRIMESTRE','2° TRIMESTRE','1° TRIMESTRE')";
		return $this->ejecutasql($qsql);		
	}
}