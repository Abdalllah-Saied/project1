<?php
require_once 'configrations/database_config.php';
require_once 'configrations/MysqlAdapter.php';
session_start();
$portfolioId= $_GET['portfolioId'];
global $config;
$connection=new MysqlAdapter($config);
if($connection->delete('portfolio',"`id`= $portfolioId")){
    header('LOCATION:manage_portfolios.php');
}else{
    header('LOCATION:manage_portfolios.php');
}
