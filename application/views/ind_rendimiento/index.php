<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>


<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Bienvenido</title>

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="<?php echo base_url();?>assets/bootstrap/css/bootstrap.min.css" integrity="sha512-dTfge/zgoMYpP7QbHy4gWMEGsbsdZeCXz7irItjcC3sPUFtf0kuFbDz/ixG7ArTxmDjLXDmezHubeNikyKGVyQ==" crossorigin="anonymous">

    <!-- Optional theme -->
    <link rel="stylesheet" href="<?php echo base_url();?>assets/bootstrap/css/bootstrap-theme.min.css" integrity="sha384-aUGj/X2zp5rLCbBxumKTCw2Z50WgIr1vs/PFN4praOTvYXWlVyh2UtNUU0KAUhAX" crossorigin="anonymous">

    <!-- <link rel="stylesheet" href="<?php echo base_url();?>assets/js/bootstrap-multiselect/dist/css/bootstrap-multiselect.css"> -->

    <style type="text/css">
        .multiselect.dropdown-toggle.btn.btn-default > div.restricted {
            margin-right: 5px;
            max-width: 100px;
            overflow: hidden;
        }
    </style>

</head>
<body>
<div class="row">
    <div class="col-md-12">
        <h2>Módulo Indicador de Rendimiento Escolar<h2>
        <h3> <?php echo $nombre_establecimiento ?> </h3>
        <h3> <?php echo $titulo?> <h3>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default" style="padding-top: 15px; padding-left: 15px; padding-bottom: 15px;">
                <div class="form-inline">
                    <div class="form-group">
                            <label for="exampleInputName2">Periodo Lectivo: </label>
                            <select class="form-control" id="select_periodo_lectivo">
                                    <?php
                                foreach ($periodos as $value) {
                                    ?>
                                    <option value="<?php echo $value['ins_per_lectivo']; ?>"><?php echo $value['ins_per_lectivo']; ?></option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="selectCurse"> <?php echo $item?> </label>
                            <select class="form-control" id="select_curso">
                                <option value="-"><?php echo $todos?></option>
                                <?php
                                foreach ($cursos as $value) {
                                    ?>
                                    <option value="<?php echo $value['id'];?>"><?php echo $value['name']; ?></option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail2">Trimestres: </label>
                            <select class="form-control" id="select_trimestre">
                                <?php
                                foreach ($trimestres as $value) {
                                    ?>
                                    <option value="<?php echo $value['id'];?>"><?php echo $value['name']; ?></option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                        <input type="hidden" value="<?php echo $nivel; ?>" id="select_nivel">
                        <input type="hidden" value="<?php echo $establecimiento; ?>" id="select_establecimiento">                        
                        <div class="form-group">
                            <button id="generar_grafico" class="btn btn-default">Generar Gr&aacute;fico</button>

                        </div>
                    </div>

            </div>
        </div>
    </div>

    <div class="row">
    	<div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body" style="text-align: center;">
                    <canvas id="myChart" width="400" height="350"></canvas>
                </div>
            </div>
    	</div>
    </div>
</div>

<script src="<?php echo base_url();?>assets/js/jquery/jquery-1.11.3.min.js"></script>

<!-- Latest compiled and minified JavaScript -->
<script src="<?php echo base_url();?>assets/bootstrap/js/bootstrap.min.js" integrity="sha512-K1qjQ+NcF2TYO/eI3M6v8EiNYZfA95pQumfvcVrTHtwQVDG+aHRqLi/ETn2uB+1JqwYqVG3LIvdm9lj6imS/pQ==" crossorigin="anonymous"></script>

<script type="text/javascript" src="<?php echo base_url();?>assets/js/chartjs/Chart.min.js"></script>

<!-- <script type="text/javascript" src="<?php echo base_url();?>assets/js/bootstrap-multiselect/dist/js/bootstrap-multiselect.js"></script> -->



<script type="text/javascript">
    base_url = '<?php echo base_url();?>index.php';

    $(document).ready(function() {

        // $('.dropdowns').multiselect({
        //     buttonWidth: '100%'
        // });
        var i;

        $('#generar_grafico').on('click', function() {

            var labels = [];
            var name_of_curse = [];

/*            if ($('#select_curso').val() === '-') {

                $("#select_curso option").each(function()
                {
                    if ($(this).val() !== '-') {
                        labels.push($(this).val());
                        name_of_curse.push($(this).text());
                    }
                }); 
                
            } else {
                name_of_curse.push($("#select_curso option[value='"+$('#select_curso ').val()+"']").text());
                labels.push('Grado_'+$('#select_curso').val());
            }*/
            
            var url=base_url+'/ind_rendimiento/getFilterIndRendimiento';
            var val_periodo_lectivo = [];
            var val_trimestre = [];
            var val_curso = [];
            var dataFilter = {};

            dataFilter.periodo_lectivo = $('#select_periodo_lectivo').val(); 
            dataFilter.trimestre       = $('#select_trimestre').val(); 
            dataFilter.curso           = $('#select_curso').val(); 
            dataFilter.nivel           = $('#select_nivel').val();
            dataFilter.establecimiento = $('#select_establecimiento').val();
            var dataValues = {};

            $.ajax({
                type: "POST",
                url: url,
                data: dataFilter,
                dataType: "JSON",
                before: function(){
                    if (dataVAlues.length > 0) {
                        dataValues.splice(0, dataValues.length);
                    }
                },
                success: function(data) {
                    dataValues = data;
                },

                complete: function() {
                    
                    var data = {
                    labels: dataValues.Materias,
                        datasets: [
                            {
                                label: "Critico",
                                fillColor: "rgba(52,157,74,0.5)",
                                strokeColor: "rgba(52,157,74,0.8)",
                                highlightFill: "rgba(52,157,74,0.75)",
                                highlightStroke: "rgba(220,220,220,1)",
                                data: dataValues.Criticos
                            },
                            {
                                label: "Riesgo", 
                                fillColor: "rgba(223,248,8,0.5)",
                                strokeColor: "rgba(223,248,8,0.8)",
                                highlightFill: "rgba(223,248,8,0.75)",
                                highlightStroke: "rgba(151,187,205,1)",
                                data: dataValues.Riesgo
                            }
                        ]
                    };

                    var myBarChart = new Chart(document.getElementById('myChart').getContext("2d")).Bar(data, options);

                }
            });

            var options = {
                // Boolean - If we should show the scale at all
                showScale: true,

                // Boolean - If we want to override with a hard coded scale
                scaleOverride: false,

                // ** Required if scaleOverride is true **
                // Number - The number of steps in a hard coded scale
                scaleSteps: 5,
                // Number - The value jump in the hard coded scale
                scaleStepWidth: 5,
                // Number - The scale starting value
                scaleStartValue: 0,

                // String - Colour of the scale line
                scaleLineColor: "rgba(0,0,0,.1)",

                // Number - Pixel width of the scale line
                scaleLineWidth: 1,

                // Boolean - Whether to show labels on the scale
                scaleShowLabels: true,

                // Interpolated JS string - can access value
                scaleLabel: "<%=value%>",

                // Boolean - Whether the scale should stick to integers, not floats even if drawing space is there
                scaleIntegersOnly: true,

                // Boolean - Whether the scale should start at zero, or an order of magnitude down from the lowest value
                scaleBeginAtZero: true,

                // String - Scale label font declaration for the scale label
                scaleFontFamily: "'Helvetica Neue', 'Helvetica', 'Arial', sans-serif",


                //String - Colour of the grid lines
                scaleGridLineColor : "rgba(0,0,0,.05)",

                //Number - Width of the grid lines
                scaleGridLineWidth : 1,

                //Boolean - Whether to show horizontal lines (except X axis)
                scaleShowHorizontalLines: true,

                //Boolean - Whether to show vertical lines (except Y axis)
                // scaleShowVerticalLines: true,

                //Boolean - If there is a stroke on each bar
                barShowStroke : true,

                //Number - Pixel width of the bar stroke
                barStrokeWidth : 2,

                //Number - Spacing between each of the X value sets
                barValueSpacing : 5,

                //Number - Spacing between data sets within X values
                barDatasetSpacing : 1,

                multiTooltipTemplate : "<%= datasetLabel %>: <%= value %>",
                responsive : false,

                //String - A legend template
                legendTemplate : "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<datasets.length; i++){%><li><span style=\"background-color:<%=datasets[i].fillColor%>\"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>"

            };

            
        });

    });
    

    

</script>
</body>