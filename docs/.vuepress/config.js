module.exports = {
  base: '/jenkins-php/',
  locales: {
    '/': {
      lang: 'zh-CN',
      title: 'Jenkins-PHP',
      description: 'Jenkins-PHP 是一个PHP语言编写的Jenkins REST API 工具库'
    },
  },
  head: [
    ['meta', { name: 'theme-color', content: '#3eaf7c' }],
    ['meta', { name: 'apple-mobile-web-app-capable', content: 'yes' }],
    ['meta', { name: 'apple-mobile-web-app-status-bar-style', content: 'black' }],
    ['meta', { name: 'msapplication-TileColor', content: '#000000' }]
  ],
  serviceWorker: true,
  themeConfig: {
    repo: 'yuan1994/jenkins-php',
    editLinks: true,
    docsDir: 'docs',
    locales: {
      '/': {
        label: '简体中文',
        selectText: 'Languages',
        editLinkText: '在 GitHub 上编辑此页',
        lastUpdated: '上次更新',
        serviceWorker: {
          updatePopup: {
            message: "发现新内容可用.",
            buttonText: "刷新"
          }
        },
        nav: [
          {
            text: '指南',
            link: '/',
          },
          {
            text: '更新日志',
            link: '/changelog.md'
          }
        ],
        sidebar: [
          {
            title: 'Jenkins-PHP',
            collapsable: false,
            children: [
              ['/guide/installing', '安装'],
              ['/guide/using', '使用'],
              ['/guide/api-reference', 'API参考'],
            ]
          }
        ]
      },
    }
  }
}
