## 安装
使用 [composer](https://getcomposer.org) 安装
```
composer require yuan1994/jenkins-php
```

::: warning
要求 PHP 版本大于等于 5.6
:::

## 文档
文档使用的是 [vuepress](https://vuepress.vuejs.org)

`yarn: `
```
yarn install
yarn build:docs
```

或者使用 `npm`:
```
npm install
npm run build:docs
```

## 单元测试
单元测试用例在 `tests` 文件夹中，可以直接运行单元测试：

```
./vendor/bin/phpunit tests/
```
