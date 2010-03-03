<?php

class dmSqlBackup extends dmConfigurable
{
  protected
  $filesystem,
  $logCallable,
  $adapters;

  public function __construct(dmFilesystem $filesystem, array $adapters, array $options)
  {
    $this->filesystem = $filesystem;
    $this->adapters   = $adapters;

    $this->initialize($options);
  }
  
  protected function initialize(array $options)
  {
    $options['dir'] = str_replace('SF_ROOT_DIR', sfConfig::get('sf_root_dir'), $options['dir']);
    
    $this->configure($options);
  }

  public function setLogCallable($callable)
  {
    $this->logCallable = $callable;

    return $this;
  }

  public function execute(Doctrine_Connection $connection)
  {
    $adapter = $this->getAdapter($connection);
    
    $infos  = $adapter->getInfos();
    $file   = $this->getFile($infos);
    
    $this->log(sprintf('About to backup %s@%s to %s', $infos['name'], $infos['host'], $file));

    $this->createDir();

    $adapter->execute($file);

    $this->log('Done.');
  }

  protected function createDir()
  {
    if(!$this->filesystem->mkdir($this->getOption('dir')))
    {
      throw new dmException(sprintf('Can NOT create dir %s', $this->getOption('dir')));
    }
  }

  protected function getFile(array $infos)
  {
    $fileName = strtr($this->getOption('file_format'), array(
      '%db_name%' => $infos['name'],
      '%year%'    => date('Y'),
      '%month%'   => date('m'),
      '%day%'     => date('d'),
      '%time%'    => date('H-i-s')
    ));

    return dmOs::join($this->getOption('dir'), $fileName);
  }

  protected function getAdapter(Doctrine_Connection $connection)
  {
    $adapterName = strtolower($connection->getDriverName());

    if(!isset($this->adapters[$adapterName]))
    {
      throw new dmException(sprintf('%s is not supported. Available adapters are %s', $adapterName, implode(', ', array_keys($this->adapters))));
    }

    return new $this->adapters[$adapterName]($this->filesystem, $connection);
  }

  protected function log($msg)
  {
    if(is_callable($this->logCallable))
    {
      call_user_func($this->logCallable, $msg);
    }
  }
}