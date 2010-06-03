<?php

abstract class dmSqlBackupAdapter
{
  protected
  $filesystem,
  $connection;

  public function __construct(dmFilesystem $filesystem, Doctrine_Connection $connection)
  {
    $this->filesystem = $filesystem;
    $this->connection = $connection;
  }

  abstract public function getInfos();

  abstract public function execute($file);
}