// @ts-check
// Note: type annotations allow type checking and IDEs autocompletion

/** @type {import('@docusaurus/types').Config} */
const config = {
  title: 'Open Connector',
  tagline: 'Connect and synchronize your data sources',
  url: 'https://openconnector.app',
  baseUrl: '/',
  
  // GitHub pages deployment config
  organizationName: 'conductionnl', 
  projectName: 'openconnector',
  trailingSlash: false,

  onBrokenLinks: 'throw',
  onBrokenMarkdownLinks: 'warn',

  // Even if you don't use internalization, you can use this field to set useful
  // metadata like html lang
  i18n: {
    defaultLocale: 'en',
    locales: ['en'],
  },

  plugins: [
    [
      'redocusaurus',
      {
        // Plugin options
        specs: [
          {
            spec: 'static/oas/open-connector.json',
            route: '/api/reference',
          },
        ],
        // Theme options for Redocusaurus
        theme: {
          // Change with your site colors
          primaryColor: '#34c4a7',
        },
      },
    ],
  ],
  
  presets: [
    [
      'classic',
      /** @type {import('@docusaurus/preset-classic').Options} */
      ({
        docs: {
          sidebarPath: require.resolve('./sidebars.js'),
          editUrl:
            'https://github.com/conductionnl/openconnector/tree/main/website/',
        },
        blog: false,
        theme: {
          customCss: require.resolve('./src/css/custom.css'),
        },
      }),
    ],
  ],

  themeConfig:
    /** @type {import('@docusaurus/preset-classic').ThemeConfig} */
    ({
      navbar: {
        title: 'Open Connector',
        logo: {
          alt: 'OpenConnector Logo',
          src: 'img/logo.svg',
        },
        items: [
          {
            type: 'docSidebar',
            sidebarId: 'tutorialSidebar',
            position: 'left',
            label: 'Documentation',
          },
          {
            href: 'https://github.com/conductionnl/openconnector',
            label: 'GitHub',
            position: 'right',
          },
        ],
      },
      footer: {
        style: 'dark',
        links: [
          {
            title: 'Docs',
            items: [
              {
                label: 'Documentation',
                to: '/docs/intro',
              },
            ],
          },
          {
            title: 'Community',
            items: [
              {
                label: 'GitHub',
                href: 'https://github.com/conductionnl/openconnector',
              },
            ],
          },
        ],
        copyright: `Copyright © ${new Date().getFullYear()} for <a href="https://openwebconcept.nl">Open Webconcept</a> by <a href="https://conduction.nl">Conduction B.V.</a>`,
      },
      prism: {
        theme: require('prism-react-renderer/themes/github'),
        darkTheme: require('prism-react-renderer/themes/dracula'),
      },
    })
};

module.exports = config;