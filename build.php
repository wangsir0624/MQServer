<?php
/**
 * make the php archive
 * @Author Wangjian
 * @Email 1636801376@qq.com
 * @Date 2017/06/19
 */
try {
    $p = new Phar('./mqserver.phar', FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME, 'mqserver.phar');
} catch(UnexpectedValueException $e) {
    exit('error: '.$e->getMessage());
}

$p->startBuffering();
$p->buildFromDirectory(__DIR__.'/src', '/.*\.php/');
$p->addFile('index.php');
$p->setDefaultStub('index.php');
$p->stopBuffering();

echo 'finished';
