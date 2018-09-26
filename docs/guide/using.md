## 使用 Jenkins-PHP
```php
use Yuan1994/Jenkins/Jenkins;

$config = ['username' => 'jenkins User ID', 'password' => 'Jenkins API token'];

$jenkins = new Jenkins('http://localhost:8080', $config);
```

## 配置
```php
<?php

/**
 * Jenkins-php配置信息
 */
return [
    // Jenkins User ID
    'username' => 'tianpian',
    // Jenkins API Token (http://jenkin.domain.com/user/{username}/configure)
    'password' => '2766bcc95aa2df67943e50b8950d65d5',
    // 是否开启CSRF保护 (系统管理/全局安全配置/CSRF Protection)
    'maybe_add_crumb' => false,
    // guzzle 配置
    'guzzle' => [
        // 接口请求超时时间(s)
        'timeout' => 5.0,
        'middleware' => [
            // 日志中间件，记录接口请求和响应，方便调试，线上建议关闭（注释）
            'log' => ['logMiddleware', 'config' => [
                'level'      => 'debug',
                'permission' => 0777,
                'file'       => '../logs/jenkins.log',
            ]],
        ]
    ],
];


```

## 示例1：获取Jenkins的版本

```php
$version = $jenkins->getVersion();

echo $version, "\n"; // 2.121.1
```

## 示例2：获取当前用户信息
```php
$whoAmi = $jenkins->getWhoAmi();

echo "id: ", $whoAmi['id'], "; ", "fullName: ", $whoAmi['fullName'], "\n";
// id: tianpian; fullName: tianpian
```

## 示例3：运行Groovy脚本
```php
$result = $jenkins->runScript('println("Hello, World!");');

echo $result; // Hello, World!
```

## 示例4：静默关闭Jenkins/取消关闭Jenkins
```php
$isSuccess = $jenkins->quietDown();

var_dump($isSuccess); // true

$isCancel = $jenkins->cancelQuietDown();

var_dump($isCancel); // true
```

## 示例5：Create/Copy/Update/Delete Jobs
```php
use Yuan1994\Jenkins\Exceptions\JenkinsException;

$configXml = file_get_contents(__DIR__.'/../data/Job/config.xml');

// 创建job
try {
    $isCreated = $jenkins->createJob('job-name', $configXml);
} catch (JenkinsException $e) {
    // job 已经存在
}

// 复制job
$isCopied = $jenkins->copyJob('from-job-name', 'to-job-name');
if ($isCopied == 404) {
    // from-job-name 不存在
}

// 重命名job
try {
    $isRenamed = $jenkins->copyJob('from-job-name', 'to-job-name');
    if ($isRenamed == 404) {
        // from-job-name 不存在
    }
} catch (JenkinsException $e) {
     // 修改后的job文件夹和原文件夹不一样
}

// 删除job
$isDeleted = $jenkins->deleteJob('job-name');
if ($isDeleted == 404) {
    // job-name 不存在
}

```

## 示例6：构建job
```php
// 简单构建
$res = $jenkins->buildJob('job-name');

// 参数化构建
$res = $jenkins->buildJob('job-name', [
          'var1' => 'val1',
          'var2' => 'val2',
          // ...
      ]);

// 也可以使用token构建
$res = $jenkins->buildJob('job-name', [], 'token');
$res = $res = $jenkins->buildJob('job-name', [
           'var1' => 'val1',
           'var2' => 'val2',
           // ...
       ], 'token');
```

更多其他的用法请参考 [API-Reference](api-reference.md)
