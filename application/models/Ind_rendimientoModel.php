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

	public function getAllMaterias($curso, $nivel)
	{
		$sqlCurso = "";
		if($nivel == 3)
		{
		$sqlCurso = "";
		$sqlNivel = "and mat_nivel = 1";
		}
		else
		{
			if (!empty($curso) && $curso != '-') 
				$sqlCurso = "and mat_curso = ".$curso;
			$sqlNivel = "and mat_nivel = ".$nivel;
		}
		$qsql = <<<EOQ
select mat_id as id, mat_descripcion as name from materia 
where 1=1 
{$sqlNivel}
{$sqlCurso}
order by mat_descripcion desc
EOQ;
		return $this->ejecutasql($qsql);
	}
	
	public function totalRegistros($periodo, $item, $trimestre, $nivel, $establecimiento)
	{		
		$sqlPeriodo = "";
		$sqlItem = "";
		$sqlTrimestre = "";
		$sqlEstablecimiento = "";

		if (!empty($periodo) && $periodo != '-') 
			$sqlPeriodo = " and i.ins_per_lectivo = ".$periodo;

		if (($nivel == 1)||($nivel == 2))
			if (!empty($item) && $item != '-') 
				$sqlItem = " and m.mat_curso = ".$item;
			if ($nivel == 3)
				{
					//$sqlItem = "  and e.est_id = ".$item;
					//$nivel = 1; 
				}

		if (!empty($trimestre) && $trimestre != '-') 
			$sqlTrimestre = " and te.tipexa_id = ".$trimestre;

		if (!empty($nivel) && $nivel != '-') 
			$sqlNivel = " and m.mat_nivel = ".$nivel;

		if (!empty($establecimiento) && $establecimiento != '-') 
			$sqlEstablecimiento = " and e.est_id = ".$establecimiento;

		$sql = <<<EOQ
select n.not_id, i.ins_per_lectivo as periodo, n.not_nota, te.tipexa_descripcion, 
m.mat_id, m.mat_descripcion, m.mat_curso, e.est_id, e.est_nombre
from nota n
 join tipo_examenes te on te.tipexa_id = n.tipexa_id 
  join docente_matplan dmp on dmp.docmatplan_id = n.docmatplan_id
   join materia_plan mp on mp.matplan_id = dmp.matplan_id
   join materia m on m.mat_id = mp.mat_id 
 join inscripcion i on i.ins_id = n.ins_id
   join oferta_academica oa on oa.ofac_id = i.ofac_id
   join establecimiento e on e.est_id = oa.est_id    
where 1=1 
 {$sqlNivel}
 {$sqlPeriodo}
 {$sqlItem}
 {$sqlTrimestre}
 {$sqlEstablecimiento}
 order by m.mat_descripcion desc
EOQ;
		return $this->ejecutasql($sql);
	}

	public function getAllPeriods()
	{
		$qsql = "select distinct(ins_per_lectivo) from inscripcion";
		return $this->ejecutasql($qsql);
	}

	public function getAllClassroom($nivel)
	{		
		if($nivel == 1) $etiqueta = " Grado";
		if($nivel == 2) $etiqueta = " Curso";
		$qsqlNivel = " cur_nivel= ".$nivel;
		$qsql = <<<EOQ
select cur_id as id, concat(cur_descripcion,'{$etiqueta}') as name from cursos 
where  {$qsqlNivel}
order by cur_descripcion
EOQ;
		return $this->ejecutasql($qsql);
	}
	public function getAllEstablecimientos()
	{
		$qsql = "select est_id as id, est_nombre as name from establecimiento order by est_nombre";
		return $this->ejecutasql($qsql);
	}
	
	public function getAllTrimestres()
	{
		$qsql = "select tipexa_id as id, tipexa_descripcion as name from tipo_examenes where tipexa_descripcion in ('3º TRIMESTRE','2º TRIMESTRE','1º TRIMESTRE') order by tipexa_descripcion";
		return $this->ejecutasql($qsql);		
	}
	public function getNameEstablecimiento($establecimiento)
	{
		$qsql = "select est_nombre as name from establecimiento where est_id = ".$establecimiento;
		return $this->ejecutasql($qsql);
	}
}