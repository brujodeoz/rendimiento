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
			$sqlPeriodo = " and extract(year from n.not_fecha_evaluacion) = ".$periodo;
		if (!empty($aula) && $aula != '-') 
			$sqlAula = " and i.ofac_id = ".$aula;
		if (!empty($trimestre) && $trimestre != '-') 
			$sqlTrimestre = " and te.tipexa_id = ".$trimestre;
		$sql = <<<EOQ
select n.not_id, extract(year from n.not_fecha_evaluacion) as periodo, n.not_nota, te.tipexa_descripcion, i.ofac_id, mp.matplan_ciclo
from nota n
 join tipo_examenes te on te.tipexa_id = n.tipexa_id 
 join docente_matplan dmp on dmp.docmatplan_id = n.docmatplan_id
 join materia_plan mp on mp.matplan_id = dmp.matplan_id
 join inscripcion i on i.ins_id = n.ins_id
where
 mp.matplan_ciclo IN ('LENGUA','MATEMATICA','CIENCIAS NATURALES','CIENCIAS SOCIALES') 
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
* No tengo en claro de que tabla y con que relaciones sacar los grados
*/
	public function getAllClassroom()
	{		
		$qsql = "SELECT distinct(ofac_id) as id, concat('Grado_',ofac_id) as name FROM inscripcion order by ofac_id";
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