# Changelog

All notable changes to this project will be documented in this file.

The format is based on Keep a Changelog, and this project adheres to Semantic Versioning.

## [0.29.0](https://github.com/tentaplane/tentapress/compare/v0.28.0...v0.29.0) (2026-02-07)


### Features

* **admin-shell:** ship distributable plugin-local admin assets ([6f3e9d3](https://github.com/tentaplane/tentapress/commit/6f3e9d354d34615d4f2063b0629d9b8d8dc257c3))

## [0.28.0](https://github.com/tentaplane/tentapress/compare/v0.27.0...v0.28.0) (2026-02-07)


### Features

* **system-info:** support Packagist URLs in plugin installer ([b497dd7](https://github.com/tentaplane/tentapress/commit/b497dd7a118ffe5ffd78dcd6f549cca5ef731352))


### Bug Fixes

* **ci:** quote release-please job condition ([1574eee](https://github.com/tentaplane/tentapress/commit/1574eee8d9e30177dac610db343d654c3181aa59))
* **release:** ignore package-only changes for monorepo release ([410ae30](https://github.com/tentaplane/tentapress/commit/410ae30b89ac6f083156763b8bc9d9cc4c0c1acf))

## [0.27.0](https://github.com/tentaplane/tentapress/compare/v0.26.0...v0.27.0) (2026-02-07)


### Features

* **admin-shell:** add subtle tentapress brand accents to admin chrome ([e1ddec2](https://github.com/tentaplane/tentapress/commit/e1ddec242578829f34ac7e8a698dff27ef456411))
* **admin-shell:** refine root typography and sidebar brand palette ([c9a4884](https://github.com/tentaplane/tentapress/commit/c9a4884a63e4959ef98fdf3ad1eabeacb5a0a67a))
* **media:** add local ingest clamp and image variants ([d6b540d](https://github.com/tentaplane/tentapress/commit/d6b540ddce0e7a53830c8fa377c2206d54fb3eb9))
* **media:** add maintenance commands for local variants ([8eac6ef](https://github.com/tentaplane/tentapress/commit/8eac6ef2c58e2f125ad6572b855112cd732e7e82))
* **system-info:** accept GitHub URLs for plugin install input ([d09109a](https://github.com/tentaplane/tentapress/commit/d09109a129bf2ba7a500ad7ad6f521104dcc6016))
* **system-info:** add queued Packagist plugin installs in admin ([56e3b06](https://github.com/tentaplane/tentapress/commit/56e3b06a4ec9749c5560a326e8610e74ab40dc8c))


### Bug Fixes

* **media:** top bar title displaying incorrectly ([f61b92c](https://github.com/tentaplane/tentapress/commit/f61b92ccb68892e3aae56bf3fd496d7e7c053555))
* **seo:** top bar title displaying incorrectly ([7082a36](https://github.com/tentaplane/tentapress/commit/7082a36cc19c2a44fa7c2a18ced2e4666c9d6348))

## [0.26.0](https://github.com/tentaplane/tentapress/compare/v0.25.1...v0.26.0) (2026-02-07)


### Features

* **media:** add async stock imports with bulk selection ([326a213](https://github.com/tentaplane/tentapress/commit/326a213f036bb07a5dae7480c7f957bc1b0acbf5))


### Bug Fixes

* **media:** mark async stock imports as imported in place ([5ab953f](https://github.com/tentaplane/tentapress/commit/5ab953f4428af8b907e7e0feef2d91575af65b45))
* **media:** prevent list-to-grid flash on media index ([454d29d](https://github.com/tentaplane/tentapress/commit/454d29dbaf827a43c2a84b99b47df251c28a31b9))

## [0.25.1](https://github.com/tentaplane/tentapress/compare/v0.25.0...v0.25.1) (2026-02-07)


### Miscellaneous Chores

* **theme-tailwind:** updated screenshot ([d636e55](https://github.com/tentaplane/tentapress/commit/d636e559fb0f9ec950a8cb72a4023b5f27fb78cc))

## [0.25.0](https://github.com/tentaplane/tentapress/compare/v0.24.0...v0.25.0) (2026-02-07)


### Features

* **blocks:** better YouTube controls and support ([a83fa90](https://github.com/tentaplane/tentapress/commit/a83fa9022ccbab7e5c601f1f42e53065b5680ebe))

## [0.24.0](https://github.com/tentaplane/tentapress/compare/v0.23.0...v0.24.0) (2026-02-07)


### Features

* **admin-shell:** add sticky header utility for admin tables ([e38c04e](https://github.com/tentaplane/tentapress/commit/e38c04e5b21a0cbf0d6795a3a19a998f4a6c019c))
* **admin-shell:** add topbar link to view site in new tab ([55a078a](https://github.com/tentaplane/tentapress/commit/55a078aa83e3b081075d971847753f776f5d4aee))


### Bug Fixes

* **admin-shell:** remove trailing full stops from toast messages ([670ac53](https://github.com/tentaplane/tentapress/commit/670ac539fa68983a6856d6e701789c149cc207fc))
* **media,menus,seo,system-info,themes,users:** enable sticky headers on list tables ([df5a6d9](https://github.com/tentaplane/tentapress/commit/df5a6d9cda434241d155c7329e31cc525de88de4))
* **media:** align grid overlay actions with shared button primitives ([af82600](https://github.com/tentaplane/tentapress/commit/af8260072c61408f807a38798fb46ac19b7a2383))
* **media:** normalize row action styling in media list ([968b1c8](https://github.com/tentaplane/tentapress/commit/968b1c881ca4375bd257976af4c7cc97fab8e9a6))
* **media:** restrict optimization services to enabled plugins ([0c74f1a](https://github.com/tentaplane/tentapress/commit/0c74f1a433a293e3bf1d056df3318f4678ca6452))
* **menus:** normalize row action styling in menus list ([7b69ae6](https://github.com/tentaplane/tentapress/commit/7b69ae615072fe058ae0cd729d48d9425b0d0857))
* **pages:** handle missing optional content and editor_driver attributes ([155de9b](https://github.com/tentaplane/tentapress/commit/155de9b530b20de2faf6438035b23bcc8216e058))
* **pages:** normalize row action styling in pages list ([5305ebf](https://github.com/tentaplane/tentapress/commit/5305ebf7b20444ed9eff22efb36bcb450a60af48))
* **posts,pages:** enable sticky headers on content list tables ([909b372](https://github.com/tentaplane/tentapress/commit/909b37201fb83c54a8159c0e483ecf2a4d710efc))
* **posts:** handle missing optional content and editor_driver attributes ([374a2a7](https://github.com/tentaplane/tentapress/commit/374a2a73adf1adc3443284829a8b52377f27db66))
* **posts:** normalize row action styling in posts list ([714ed89](https://github.com/tentaplane/tentapress/commit/714ed89459220bf69e42e191d7562a6ebd5c6e88))
* **seo:** normalize row action styling in seo list ([26190e3](https://github.com/tentaplane/tentapress/commit/26190e39c35edae0d53fdc18a8a9f065ff3cd18e))
* **seo:** use status badge primitive in seo index list ([c2f67fc](https://github.com/tentaplane/tentapress/commit/c2f67fcef998858af559bf117d4e72e372ce3cfd))
* **system-info:** use status badge primitives in diagnostics tables ([3538ebb](https://github.com/tentaplane/tentapress/commit/3538ebb27a1a707c3e4d2d89c423a6112823c675))
* **themes:** normalize row action styling in themes list ([5549390](https://github.com/tentaplane/tentapress/commit/55493908702b6f98ce935845931c827142540821))
* **themes:** use status badge primitives in themes list ([cbe7f74](https://github.com/tentaplane/tentapress/commit/cbe7f74eae4f46a82bed1c5696d13f188a5f2b5b))
* **users:** normalize list action and neutral badge styling ([ac131bc](https://github.com/tentaplane/tentapress/commit/ac131bc306089036965d7ca96c1b636e6fcf2533))

## [0.23.0](https://github.com/tentaplane/tentapress/compare/v0.22.0...v0.23.0) (2026-02-07)


### Features

* **blocks:** promoted split image/content block to first party ([bb9c904](https://github.com/tentaplane/tentapress/commit/bb9c904f23d18502170f2a63f0615b11e8006c29))
* **system:** updated and released system package ([6f27e6a](https://github.com/tentaplane/tentapress/commit/6f27e6a1ccf455acbeff284e6e70aaaa921aee17))

## [0.22.0](https://github.com/tentaplane/tentapress/compare/v0.21.0...v0.22.0) (2026-02-07)


### Features

* **admin-shell:** add compact badge primitives for admin status chips ([5b3ad3b](https://github.com/tentaplane/tentapress/commit/5b3ad3bf2a2f1264b698472943ce0054cda7d46c))
* **menus:** add drag-and-drop sorting to menu items ([f677c92](https://github.com/tentaplane/tentapress/commit/f677c921e45fe2d0fcf2da762d5eab0ad8adc6da))
* **menus:** modernize menus index experience ([dd2f302](https://github.com/tentaplane/tentapress/commit/dd2f30265d2d42505117af75901e1b169f946aab))
* **system:** better loading of Blade directives, some security best practices and rate limiting login ([ca7b383](https://github.com/tentaplane/tentapress/commit/ca7b383512eeccd64cd2b2076027a510c510aad6))


### Bug Fixes

* **admin-shell:** add focus-visible states to admin button primitives ([a8bf65c](https://github.com/tentaplane/tentapress/commit/a8bf65cbc972d1ece003cf41edbd3e647af8d08f))
* **admin-shell:** align dashboard header with shared page primitives ([31b128b](https://github.com/tentaplane/tentapress/commit/31b128b013d6c45efc7f8ddbddeb85318a9fccfa))
* **admin-shell:** improving the Admin UI ([72f2d8d](https://github.com/tentaplane/tentapress/commit/72f2d8d5b6390a0f79e2d219e13052af2226b8a4))
* **import:** adopt media upload component for bundle input ([558bc6a](https://github.com/tentaplane/tentapress/commit/558bc6ac329d11a46f0b372dacbf5e8309eb659f))
* **media:** delete storage file when removing media ([51dec3e](https://github.com/tentaplane/tentapress/commit/51dec3e59837b76034c354a7c5cf804655bbf4af))
* **media:** hardening the media requests for third parties ([7a93d2b](https://github.com/tentaplane/tentapress/commit/7a93d2b8db55f0f504e593ce049389ada41cfd17))
* **menus:** match block editor action icons ([394b910](https://github.com/tentaplane/tentapress/commit/394b91003700736e1007ae141bc69d5f0c67e9ea))
* **menus:** use icon controls for menu item actions ([18d0491](https://github.com/tentaplane/tentapress/commit/18d0491c44bd1fb364522c4a50e64a5026a0887e))
* **pages:** use status badge primitives in pages list ([87a1920](https://github.com/tentaplane/tentapress/commit/87a19206127bca2584f662b8fd98d37af22328de))
* **posts:** use status badge primitives in posts list ([0a2334d](https://github.com/tentaplane/tentapress/commit/0a2334dc37a4276c6916d107b20577c12d642f75))
* **system-info:** plugin enable/disable is now async ([4143bac](https://github.com/tentaplane/tentapress/commit/4143bac2f56164cc5b0843745437f9cf69eeb405))
* **system:** increase the hardening and defaults for the system ([5a63633](https://github.com/tentaplane/tentapress/commit/5a6363343f9d5f7a42f043b1f1d8458b29856965))
* **themes:** highlight active theme row ([06e0128](https://github.com/tentaplane/tentapress/commit/06e01284f27dd854c3491e60cec00a43ccae7b19))
* **themes:** pin active theme to top of list ([bcb642e](https://github.com/tentaplane/tentapress/commit/bcb642e1c7d934b30e7c3fbfdced8dfe6997ef4f))
* **users:** include rate limiting via system for the login endpoint ([a6ef00a](https://github.com/tentaplane/tentapress/commit/a6ef00ac0c078db9ec6d6a6667ab17d7de7ee331))
* **users:** normalize users and roles search fields to shared input styles ([4169041](https://github.com/tentaplane/tentapress/commit/41690411a90151fac78d78c11d462da7c2afca4e))


### Miscellaneous Chores

* **admin-shell:** admin UI assets ([34fa6aa](https://github.com/tentaplane/tentapress/commit/34fa6aa6afb0470d08320ded7751f14f8d116af0))
* **theme-tailwind:** update theme screenshot ([61f7d6a](https://github.com/tentaplane/tentapress/commit/61f7d6a4d0561fb7e6baff1b0e65fad916354c1b))

## [0.21.0](https://github.com/tentaplane/tentapress/compare/v0.20.0...v0.21.0) (2026-02-07)


### Features

* **blocks:** add repeater-based editors for list fields ([5c94983](https://github.com/tentaplane/tentapress/commit/5c949833838b319ce627eca216e5e3f37edbe549))


### Bug Fixes

* **admin-shell:** simplify shell copy and bump patch version ([30a1ca0](https://github.com/tentaplane/tentapress/commit/30a1ca07d15bdae9b211d78f9b71703bf8ca676c))
* **blocks:** simplify blocks editor copy ([71c0ee9](https://github.com/tentaplane/tentapress/commit/71c0ee9af31f4e2fbbcb2263e8b923dfcb4c54f2))
* **export:** simplify export copy and bump patch version ([568528c](https://github.com/tentaplane/tentapress/commit/568528c346e5c4d6b1a73a2268796f0287eb6f5c))
* **import:** simplify import copy and bump patch version ([d3f2e92](https://github.com/tentaplane/tentapress/commit/d3f2e92b5ada20fbb9fee72e60682ed93afa5406))
* **media-stock-pexels:** simplify settings copy ([c75a916](https://github.com/tentaplane/tentapress/commit/c75a916ff6a54df2b16c217cc9d9b9e6f34f8949))
* **media-stock-unsplash:** simplify settings copy ([4db4e8e](https://github.com/tentaplane/tentapress/commit/4db4e8e5ff61adc57035d5d91a004adc109ca138))
* **media:** correct pexels video preview in stock modal ([fd78658](https://github.com/tentaplane/tentapress/commit/fd7865839c794195826a7de460cdd9a49d8c1e6e))
* **media:** simplify media admin copy ([1921caa](https://github.com/tentaplane/tentapress/commit/1921caa022d3001aaf842638d64655694a3cc77b))
* **menus:** simplify menu admin copy ([d2d4ad9](https://github.com/tentaplane/tentapress/commit/d2d4ad9c9af41b21b3504243cb5b5705fb8b46c7))
* **page-editor:** simplify editor copy ([09c0ef8](https://github.com/tentaplane/tentapress/commit/09c0ef8b1c8c6f958cf636ebd1b6a8acc9febe3a))
* **pages:** simplify admin page copy ([ac99401](https://github.com/tentaplane/tentapress/commit/ac994013ef3a0ce6319b6eca78c2d26fb59c2a66))
* **posts:** simplify admin post copy ([15791a4](https://github.com/tentaplane/tentapress/commit/15791a4478c219f3bad65aedecdde0d2773b8b77))
* **seo:** simplify seo admin copy ([dd8bb7a](https://github.com/tentaplane/tentapress/commit/dd8bb7abab4f69ffb6aa149b98fbb7715e023e6e))
* **settings:** simplify settings copy and bump patch version ([734646e](https://github.com/tentaplane/tentapress/commit/734646ee0abdc1aa52a109f020c5ff12697925a9))
* **static-deploy:** simplify deploy admin copy ([55c285f](https://github.com/tentaplane/tentapress/commit/55c285fc4d296383c64af965b7cb97bbc3770102))
* **system-info:** simplify admin copy and bump patch version ([db8f030](https://github.com/tentaplane/tentapress/commit/db8f0304a90374d5419afdc55d2e218e8a9732fc))
* **themes:** simplify themes copy and bump patch version ([a644dd4](https://github.com/tentaplane/tentapress/commit/a644dd4553622baee4992c1c9e74063144389c72))
* **users:** simplify users and roles admin copy ([681fba2](https://github.com/tentaplane/tentapress/commit/681fba28a325a19472f5d099ac89df9ce7e4058e))


### Miscellaneous Chores

* **admin-shell:** build assets ([b9feebd](https://github.com/tentaplane/tentapress/commit/b9feebdb105a86d293ff8d68a59e1d18f5746e42))

## [0.20.0](https://github.com/tentaplane/tentapress/compare/v0.19.0...v0.20.0) (2026-02-06)


### Features

* **media-optimization-bunny:** add provider plugin ([ca8c9a0](https://github.com/tentaplane/tentapress/commit/ca8c9a0395d42723afd11342b8604998c2533a7c))
* **media-optimization-cloudflare:** add provider plugin ([1dcd7e8](https://github.com/tentaplane/tentapress/commit/1dcd7e866680fadbc5a4902d5c512a16f5737deb))
* **media-optimization-imagekit:** add provider plugin ([022cadc](https://github.com/tentaplane/tentapress/commit/022cadc16dce4d78536c5e50e2b1178944a1410c))
* **media-optimization-imgix:** add provider plugin ([5dd0adf](https://github.com/tentaplane/tentapress/commit/5dd0adf0db53465f8a3c8c2e1a3f266c7d99da83))
* **media-stock-pexels:** add provider plugin ([7447976](https://github.com/tentaplane/tentapress/commit/7447976a487b3f67bf9a14de557480995eea34cd))
* **media-stock-unsplash:** add provider plugin ([38fe813](https://github.com/tentaplane/tentapress/commit/38fe813960fa31ebc6a2747d568f502be7faf335))
* **media:** add optimization providers ([d491f48](https://github.com/tentaplane/tentapress/commit/d491f4878bf73d2d1501ec3dfe97bc92158f66ee))
* **media:** add optimization settings ([9504ca9](https://github.com/tentaplane/tentapress/commit/9504ca9bb9f4b7fd6afde669e8c2e5d7d1d3d134))
* **media:** refactor stock providers ([92746ba](https://github.com/tentaplane/tentapress/commit/92746ba0b5967d343f2ceb1fba921e8c1db981bb))


### Bug Fixes

* **ci:** fix for actions not running for plugin distribution ([4003fd7](https://github.com/tentaplane/tentapress/commit/4003fd7e124a627da289ffa8c2ac7648d17684d2))
* **media:** promote stock libraries and optimisations to menu items ([7314f83](https://github.com/tentaplane/tentapress/commit/7314f8348d80519e95f98be5ca7c32c3d1053589))
* **theme-tailwind:** before and after custom block fixed ([99babf2](https://github.com/tentaplane/tentapress/commit/99babf294a580fcfd8fd18d4e9d989bd6bea532d))


### Miscellaneous Chores

* **media:** apply cleanup ([958ef1f](https://github.com/tentaplane/tentapress/commit/958ef1f0495eecdf5a4aa76fdee8f8dc48c8a1ab))
* **media:** normalize optimization redirect ([96d1cbb](https://github.com/tentaplane/tentapress/commit/96d1cbbe5e29ed48da99948782d008cebeaf1211))
* **theme-tailwind:** new clone script for creating copies of the base Tailwind theme ([8ca8405](https://github.com/tentaplane/tentapress/commit/8ca840500859b5e546e3762a18572d4c717830a5))

## [0.19.0](https://github.com/tentaplane/tentapress/compare/v0.18.1...v0.19.0) (2026-02-06)


### Features

* **media:** now includes stock media availability ([af2033c](https://github.com/tentaplane/tentapress/commit/af2033c30d3483dcd3fd84f55ae75629e5aa8fd8))
* **seo:** improved UI and promoted to full feature ([3a64e7b](https://github.com/tentaplane/tentapress/commit/3a64e7b0eb80f2e2a27807a2984210379fd0cf36))
* **system:** ensure migrations run when plugins are synced or cached ([ed8a5f8](https://github.com/tentaplane/tentapress/commit/ed8a5f82b5a1e7615c4ff865e507b42227ce2c21))


### Bug Fixes

* **admin-shell:** better UI for alert/confirm dialogues ([843233a](https://github.com/tentaplane/tentapress/commit/843233ae1996e96e883a55c84a2e513d718776b1))
* **blocks:** improve UI save states for collapse/expand ([bb6398d](https://github.com/tentaplane/tentapress/commit/bb6398df69657111dfed7119efcba323825ba7a1))
* **import:** new dialog modal ([58ab995](https://github.com/tentaplane/tentapress/commit/58ab9951c3d394f7b8d59660f6c32112834409bb))
* **media:** improved search for stock images ([52f89bb](https://github.com/tentaplane/tentapress/commit/52f89bbadb170a2ce5378130b7448a72f572b704))
* **pages:** new dialog modal ([2a053c6](https://github.com/tentaplane/tentapress/commit/2a053c6b4d9910d4a397f93699aa5d82ddd8bc2d))
* **posts:** new dialog modal ([b1ad7ba](https://github.com/tentaplane/tentapress/commit/b1ad7ba935c73d79af098d94f3910129650c1772))
* **static-deploy:** new dialog modal ([bd17f0e](https://github.com/tentaplane/tentapress/commit/bd17f0e5f6f9209e65afed6caa18ad6e0874cf85))
* **users:** new dialog modal ([3f4fb9f](https://github.com/tentaplane/tentapress/commit/3f4fb9f009538e0aa40f0712d089fd692399f7ad))


### Miscellaneous Chores

* **custom-blocks:** improved the documentation of field types available ([9b1e4d1](https://github.com/tentaplane/tentapress/commit/9b1e4d1ec660d89fee1e04bf5856aeb507562a32))

## [0.18.1](https://github.com/tentaplane/tentapress/compare/v0.18.0...v0.18.1) (2026-02-05)


### Bug Fixes

* **theme-tailwind:** updated theme with hot reload ([05f1c3c](https://github.com/tentaplane/tentapress/commit/05f1c3c03c7b3940930cdc7f88c8f338054ecf24))


### Miscellaneous Chores

* **plugins:** update with placeholder 3rd party notice ([914fee4](https://github.com/tentaplane/tentapress/commit/914fee42c52de230ed342241dcc35500fde6907d))

## [0.18.0](https://github.com/tentaplane/tentapress/compare/v0.17.0...v0.18.0) (2026-02-05)


### Features

* **theme:** example custom blocks ([bd57eab](https://github.com/tentaplane/tentapress/commit/bd57eabad7ade72de8f39094cd5808e3f8232bba))


### Bug Fixes

* **admin-shell:** switching to toasts for flash messages ([7430067](https://github.com/tentaplane/tentapress/commit/7430067c4af7ee5f035ee43d08b08db62257c251))
* **blocks:** UI improvements for full screen editing and JSON editing ([3c62dd3](https://github.com/tentaplane/tentapress/commit/3c62dd3d740a130f4e2c7f993073571e38a29f79))
* **page-editor:** UI cleanup ([04121ed](https://github.com/tentaplane/tentapress/commit/04121ed24b32e1c8ad2302ff6b9a88dd7563cc3e))
* **pages:** improved admin UI ([b176d99](https://github.com/tentaplane/tentapress/commit/b176d99c95af3d27455d77dfaadec1e2de0f2b7a))
* **pages:** minor UI improvements ([8d606fa](https://github.com/tentaplane/tentapress/commit/8d606fa854837fdea8096e8ca81f3cf9fdf35f8e))
* **posts:** improved admin UI ([762c302](https://github.com/tentaplane/tentapress/commit/762c3028aead5feffa9c84b31fcf7ca902365851))
* **posts:** minor UI improvements ([126cf7c](https://github.com/tentaplane/tentapress/commit/126cf7c88c54ed8347c32ece9170003e16cdfd0d))
* **themes:** Theme UI improvements ([8d1d46b](https://github.com/tentaplane/tentapress/commit/8d1d46b291dbf961db2681f223d58499efd7b0cf))


### Miscellaneous Chores

* **custom-blocks:** PHP code linting ([535196b](https://github.com/tentaplane/tentapress/commit/535196bb2ad42e17a2c2406c3629baf6904f85cf))
* **export:** PHP code linting ([9bf7641](https://github.com/tentaplane/tentapress/commit/9bf7641de87a32fdb26fd16121868f174667064f))
* **import:** PHP code linting ([0276a7a](https://github.com/tentaplane/tentapress/commit/0276a7a85ab0c99d8a101c6db599e80c5cdf7863))
* **page-editor:** PHP code linting ([2ab3fd6](https://github.com/tentaplane/tentapress/commit/2ab3fd658803e9abbe9035332cd7fec953dfe3e8))
* **seo:** PHP code linting ([53c1c3f](https://github.com/tentaplane/tentapress/commit/53c1c3f32a5edbfc7500784b6f7d6d92f1b7b4e6))
* **system:** Admin UI assets ([e8cbc97](https://github.com/tentaplane/tentapress/commit/e8cbc97be0696ce0631f76cc5d0683d378b2a88f))
* **system:** PHP code linting ([2e33ec4](https://github.com/tentaplane/tentapress/commit/2e33ec47af5fa9c6630b49787a2c49b8796efd5e))

## [0.17.0](https://github.com/tentaplane/tentapress/compare/v0.16.1...v0.17.0) (2026-02-05)


### Features

* **admin-shell:** new improved stats and shortcuts on the dashboard ([ce7b145](https://github.com/tentaplane/tentapress/commit/ce7b1457245714b36318a2f7209e69355194d255))
* **custom-blocks:** single-file block discovery for active themes ([8c63ccc](https://github.com/tentaplane/tentapress/commit/8c63cccf520d55b1410c317690012ffd77326136))


### Bug Fixes

* **theme-tailwind:** small tweaks to header/footer ([392c1fc](https://github.com/tentaplane/tentapress/commit/392c1fc6e3725944bb9a83ad1c06329e133a5092))


### Miscellaneous Chores

* **system:** new first-party plugin suggestions ([108b437](https://github.com/tentaplane/tentapress/commit/108b437f25db95ab1291f401e4b51bfad8f73ded))

## [0.16.1](https://github.com/tentaplane/tentapress/compare/v0.16.0...v0.16.1) (2026-02-05)


### Bug Fixes

* **system:** minor changes to the demo homepage seeder ([4ad1b79](https://github.com/tentaplane/tentapress/commit/4ad1b7999091401d7bb7e04204d2ada7a22aa38d))
* **theme-tailwind:** improved layouts ([0e2c03d](https://github.com/tentaplane/tentapress/commit/0e2c03d6eb2b4f2e02baf8c9b5615f3ed3984a67))

## [0.16.0](https://github.com/tentaplane/tentapress/compare/v0.15.6...v0.16.0) (2026-02-05)


### Features

* **media:** small improvements to the Media plugin UI ([0259ac0](https://github.com/tentaplane/tentapress/commit/0259ac01867cb3f6f8b464274f3c133ae23e2927))
* **page-editor:** Full working continuous page editor ([d9134f6](https://github.com/tentaplane/tentapress/commit/d9134f6b91d26af48d341278abf2f02d77d4b60a))
* **pages:** Support for the new first-party page editor ([1b1c1f1](https://github.com/tentaplane/tentapress/commit/1b1c1f10ad62b021642259e0c524707c60db5a89))
* **posts:** Support for the new first-party page editor ([c2df162](https://github.com/tentaplane/tentapress/commit/c2df162e46a631b79e7dc959957c7863a26d5389))
* **system:** Improved plugin management and asset syncing ([c775b23](https://github.com/tentaplane/tentapress/commit/c775b237f33a68135c37d5e4441b534f7e0fd563))
* **system:** new command to seed a demo homepage ([4c040bc](https://github.com/tentaplane/tentapress/commit/4c040bccf7dfe94893fc9300c5b8405bc90c9615))


### Bug Fixes

* **blocks:** rendering blocks after switching to the page editor and back again wasn’t correct ([95d6bd0](https://github.com/tentaplane/tentapress/commit/95d6bd000a682425a2d3cc7a7424db0648663622))
* **pages:** improvements to the Pages forms when enabling/disabling the Page Editor plugin ([95ffe93](https://github.com/tentaplane/tentapress/commit/95ffe9350bf64cbcee998d47f938e14923d0a7bc))
* **plugins:** better sorting of the plugins in the UI ([10791c5](https://github.com/tentaplane/tentapress/commit/10791c537468843724ebc240d9e09747886b6d62))
* **posts:** improvements to the Posts forms when enabling/disabling the Page Editor plugin ([48a9e97](https://github.com/tentaplane/tentapress/commit/48a9e970b399411cb6a71a5493de922f57f80847))
* **themes:** minor improvement to the Themes UI ([b0c5251](https://github.com/tentaplane/tentapress/commit/b0c525101508eb360ecbf2d94fec28c6af777ecb))


### Miscellaneous Chores

* **media-bunny:** removing the Bunny CDN integration for now ([d96e8ba](https://github.com/tentaplane/tentapress/commit/d96e8ba0e95872a8a8022f1455a7fc1f8f75fc15))

## [0.15.6](https://github.com/tentaplane/tentapress/compare/v0.15.5...v0.15.6) (2026-02-04)


### Bug Fixes

* **blocks:** improved UX, includes save keyboard command ([c4b6439](https://github.com/tentaplane/tentapress/commit/c4b6439280a77c7fe738809254d298a894ec38d1))
* **theme-tailwind:** UI improves to theme blocks ([f6e0787](https://github.com/tentaplane/tentapress/commit/f6e078765a16ab5ca6a5aaafcea71318924fa851))

## [0.15.5](https://github.com/tentaplane/tentapress/compare/v0.15.4...v0.15.5) (2026-02-04)


### Bug Fixes

* **blocks:** better full-screen editor UI ([df80b4c](https://github.com/tentaplane/tentapress/commit/df80b4c5deb122a1fa4d3a9a637617adc2cc7d02))
* **setup:** seed demo settings ([8691585](https://github.com/tentaplane/tentapress/commit/869158588c1a0ded114b20f46e1b3bff6436e216))
* **setup:** updating homepage ID setting not required ([fb9262d](https://github.com/tentaplane/tentapress/commit/fb9262d8db33e0113a978c522741e0fd135a6ad8))
* **theme-tailwind:** simplify the stats block ([9524f2a](https://github.com/tentaplane/tentapress/commit/9524f2af6551c58479310fa8df1a89f617db8116))

## [0.15.4](https://github.com/tentaplane/tentapress/compare/v0.15.3...v0.15.4) (2026-02-04)


### Bug Fixes

* **blocks:** media modal was missing from editors ([096a1e7](https://github.com/tentaplane/tentapress/commit/096a1e778fed462f66b9888db7a0089856d1e688))
* **blocks:** UI improvements to the expanded/collapsed blocks ([77fe2b6](https://github.com/tentaplane/tentapress/commit/77fe2b67a85d0796ce19306dc4ef2b6bb80dc8ed))
* **setup:** cleaning up output scripts ([354c608](https://github.com/tentaplane/tentapress/commit/354c60844a4a3eb1288b5cf4f51b1e3d1c580aac))
* **tailwind-theme:** full width hero improvements ([e139415](https://github.com/tentaplane/tentapress/commit/e1394156dd4b66b45a63227e8949a606e6cfe815))

## [0.15.3](https://github.com/tentaplane/tentapress/compare/v0.15.2...v0.15.3) (2026-02-04)


### Miscellaneous Chores

* **setup:** clean up zip archive outputs ([4ce4196](https://github.com/tentaplane/tentapress/commit/4ce419667ac8de6bc8a8b6e0cd1889d38bff8c35))

## [0.15.2](https://github.com/tentaplane/tentapress/compare/v0.15.1...v0.15.2) (2026-02-04)


### Bug Fixes

* **themes:** added a theme sync button to the admin UI ([015c805](https://github.com/tentaplane/tentapress/commit/015c80508c7441688a27a8f4ffed9bac9ca9ab4e))


### Miscellaneous Chores

* **themes:** updated base tailwind screenshot ([e8a4cc6](https://github.com/tentaplane/tentapress/commit/e8a4cc64c9afd466e8dd037e7ffccfd45ae80f5a))

## [0.15.1](https://github.com/tentaplane/tentapress/compare/v0.15.0...v0.15.1) (2026-02-04)


### Bug Fixes

* **export:** plugins weren’t exported correctly ([8d48b4d](https://github.com/tentaplane/tentapress/commit/8d48b4d2cb62eab7f9230d01ed2c64ac2145f629))
* **pages:** caveat if page editor isn’t installed ([adc6737](https://github.com/tentaplane/tentapress/commit/adc673777bf51083e0319305210ef4ce6bc1e229))
* **posts:** caveat if page editor isn’t installed ([6c639c7](https://github.com/tentaplane/tentapress/commit/6c639c7b2b8180ff9c1e6b73676490ea22796655))


### Miscellaneous Chores

* **setup:** gitignore restructured ([8bc478f](https://github.com/tentaplane/tentapress/commit/8bc478f45736d7978001d8f62640941ed47e2a29))

## [0.15.0](https://github.com/tentaplane/tentapress/compare/v0.14.0...v0.15.0) (2026-02-04)


### Features

* **block-markdown-editor:** update assets ([3d28578](https://github.com/tentaplane/tentapress/commit/3d28578024cac35f23b79e9635bbde68e87b062f))
* **blocks:** update editor ([77298d2](https://github.com/tentaplane/tentapress/commit/77298d2eb3bf0ed525ba64ce5ccce955fc9b50ae))
* **page-editor:** add plugin ([837ef6b](https://github.com/tentaplane/tentapress/commit/837ef6bc046245f58c05ccb6fa3f3f5ef9703260))
* **pages:** update editor flow ([51ac7c6](https://github.com/tentaplane/tentapress/commit/51ac7c67dad663cffa4fda1236d029d1367bc790))
* **posts:** update editor flow ([0cc1c2f](https://github.com/tentaplane/tentapress/commit/0cc1c2f512df707820da21ca0465f8d46ea5ece5))
* **system:** publish plugin assets ([ec1b565](https://github.com/tentaplane/tentapress/commit/ec1b5654d880310b524e6465caa352ac631ea183))
* **theme-tailwind:** refresh blocks ([f042be2](https://github.com/tentaplane/tentapress/commit/f042be2e769fa175791b109cb230057870a821c2))


### Miscellaneous Chores

* **build:** refresh public assets ([b0f4813](https://github.com/tentaplane/tentapress/commit/b0f48133ebf04f8bc8a55745955864cbb7388549))
* **core:** update tooling and docs ([a43ea00](https://github.com/tentaplane/tentapress/commit/a43ea00ee7421d66e6a6beaa10e1225e0260dc0d))
* **setup:** better plugin asset management ([4ee0696](https://github.com/tentaplane/tentapress/commit/4ee0696e0133ff5669347eeb4b9e94235b03d943))
* **theme-bootstrap:** remove bootstrap theme ([6826593](https://github.com/tentaplane/tentapress/commit/682659398e8b8d7e1e761e87306f33d6fc1e2e13))

## [0.14.0](https://github.com/tentaplane/tentapress/compare/v0.13.1...v0.14.0) (2026-02-03)


### Features

* improved default theme ([e7d4b22](https://github.com/tentaplane/tentapress/commit/e7d4b2293f2b8daf67cd6ee7d4d7c540e15edfd0))


### Bug Fixes

* **setup:** fixes block editor display ([0098641](https://github.com/tentaplane/tentapress/commit/0098641997e924b38d4b7ab318a8f7e6153942bf))

## [0.13.1](https://github.com/tentaplane/tentapress/compare/v0.13.0...v0.13.1) (2026-02-03)


### Bug Fixes

* **setup:** minor update to markdown editor release notes ([937da36](https://github.com/tentaplane/tentapress/commit/937da363d68c8dcfa7e169f2d9abe3bbc5710d81))

## [0.13.0](https://github.com/tentaplane/tentapress/compare/v0.12.0...v0.13.0) (2026-02-03)


### Features

* **setup:** significantly updated and refined setup script ([ca08a51](https://github.com/tentaplane/tentapress/commit/ca08a51bdfb25976a25dbda5e938fd314439425d))

## [0.12.0](https://github.com/tentaplane/tentapress/compare/v0.11.0...v0.12.0) (2026-02-03)


### Features

* **blocks:** restyle hero content features ([b0fbd1c](https://github.com/tentaplane/tentapress/commit/b0fbd1c231e5819cbe73cdbce19608a058497e76))
* **setup:** seed full demo page ([8ffd317](https://github.com/tentaplane/tentapress/commit/8ffd317a64a3c1e9684439b62f55ee43319ba363))
* **theme:** amplify base theme ([5fc7aa1](https://github.com/tentaplane/tentapress/commit/5fc7aa127db175e3df5e71464840df830cecd46e))
* **theme:** make hero full width ([2a9f643](https://github.com/tentaplane/tentapress/commit/2a9f6437cbba55da6fb7162e8084f9c6511215c0))
* **theme:** refresh base theme shell ([0a1258a](https://github.com/tentaplane/tentapress/commit/0a1258a2463fc4db10688c0ded1e9e9e599a94a1))
* **theme:** restyle cta testimonial logo-cloud ([838b552](https://github.com/tentaplane/tentapress/commit/838b552db34b068e655d726a467f835f10c17efc))
* **theme:** restyle gallery image buttons newsletter ([75b450c](https://github.com/tentaplane/tentapress/commit/75b450c19279a0f37b775f4241093170d343905d))
* **theme:** restyle stats faq quote ([f7448dc](https://github.com/tentaplane/tentapress/commit/f7448dc872060e94bc6cbd6619a8d9b982d5cb15))
* **theme:** restyle timeline table embed map divider ([6cf5dc0](https://github.com/tentaplane/tentapress/commit/6cf5dc0eaf840aebe904e93d3c604458980592b4))


### Bug Fixes

* **blocks:** show variant selection ([b903d87](https://github.com/tentaplane/tentapress/commit/b903d8749c56de0aa71a3b203e220506e6fe9b23))
* **blocks:** sync select values ([9c995b4](https://github.com/tentaplane/tentapress/commit/9c995b41b0c71b45e8df3e912c093c9aea4ddd6e))
* **theme:** align vite dev server urls ([a92f886](https://github.com/tentaplane/tentapress/commit/a92f886e3139b65ddf194d291395f6380ac9273b))
* **theme:** load vite dev assets ([8b7e9bd](https://github.com/tentaplane/tentapress/commit/8b7e9bddaa99ac030b6f66fc4813c4bd6560e349))
* **theme:** move block overrides into theme ([1c7ef30](https://github.com/tentaplane/tentapress/commit/1c7ef30b41a60c35c76af6150ed9351c5e531f52))
* **theme:** read custom hot file ([e69fe57](https://github.com/tentaplane/tentapress/commit/e69fe5796cceafaad38243869364a5ad867e8062))
* **theme:** skip hot reload assets ([9e527b8](https://github.com/tentaplane/tentapress/commit/9e527b8446ac5c9349286d34d46591f60acf5b22))


### Miscellaneous Chores

* **theme:** remove dev script ([1439b48](https://github.com/tentaplane/tentapress/commit/1439b4809b01149baf777ddd6419e1b395f0fe2b))

## [0.11.0](https://github.com/tentaplane/tentapress/compare/v0.10.1...v0.11.0) (2026-02-03)


### Features

* **admin:** add asset stacks ([576475e](https://github.com/tentaplane/tentapress/commit/576475e4aadd62a1aa7c026c4817f69ab5147ec7))
* **blocks:** add markdown editor plugin ([b8ae3d9](https://github.com/tentaplane/tentapress/commit/b8ae3d946086d4dbb2c15787063ce1178e0290c8))
* **blocks:** add rich text block plugin ([68345ca](https://github.com/tentaplane/tentapress/commit/68345ca312b93054079d87a445d522b51ec4db8d))
* **editor:** order blocks by origin ([289f8ed](https://github.com/tentaplane/tentapress/commit/289f8edb2e7f3410be2677a65a0503d63df8c0d3))


### Bug Fixes

* **editor:** keep fullscreen on save ([52164d6](https://github.com/tentaplane/tentapress/commit/52164d67dc29ba88b187870798636a19ed93d4b3))
* **markdown-editor:** remove image toolbar ([4e318c0](https://github.com/tentaplane/tentapress/commit/4e318c01fa01f5e43dfb393b908def71918ba7a3))
* **setup:** removing rich text block, needs a different approach ([8e49646](https://github.com/tentaplane/tentapress/commit/8e496464bb8934c8bcc1dd7f549a6d3f748c495a))
* **setup:** Updated documentation ([79c9b86](https://github.com/tentaplane/tentapress/commit/79c9b863d524d52f8d185c73031fcefe0a355b36))

## [0.10.1](https://github.com/tentaplane/tentapress/compare/v0.10.0...v0.10.1) (2026-02-02)


### Miscellaneous Chores

* **plugins:** bump blocks/pages/posts versions ([1e81089](https://github.com/tentaplane/tentapress/commit/1e8108920ba3c094f2c069e510a2c1c158e0a615))

## [0.10.0](https://github.com/tentaplane/tentapress/compare/v0.9.1...v0.10.0) (2026-02-02)


### Features

* **editor:** add posts full-screen mode ([3463493](https://github.com/tentaplane/tentapress/commit/346349344d2a4e543ea34e893a92e526c61c2c3c))
* **editor:** style full-screen blocks ([965b97d](https://github.com/tentaplane/tentapress/commit/965b97d5e855b868ee6114af3f3c10b06151eacd))
* **pages:** add draggable block palette ([ad9664f](https://github.com/tentaplane/tentapress/commit/ad9664f44a527622c3578a77f6b6ed36fe17ae4f))
* **pages:** add editor sidebar outline ([64e0f34](https://github.com/tentaplane/tentapress/commit/64e0f343326768eb25eb330cd16363e6dd40c042))
* **pages:** add full-screen blocks editor ([6f1013c](https://github.com/tentaplane/tentapress/commit/6f1013cdbb801d21989605efe7f2296d5d464411))
* **pages:** enable true fullscreen editor ([e1ec142](https://github.com/tentaplane/tentapress/commit/e1ec1427fa717877f4d0bafda0536e73f0069a04))
* **pages:** polish full-screen blocks editor ([6dedf8b](https://github.com/tentaplane/tentapress/commit/6dedf8bf6d07a8c8455321ea50b305338793d968))
* **pages:** show drop markers while dragging ([93189e2](https://github.com/tentaplane/tentapress/commit/93189e22161f8b20c3e353ed4a22079555af76a5))
* **plugin:** add acme events schedule block ([51820ac](https://github.com/tentaplane/tentapress/commit/51820ac53a5e8dfe77f53e51c3fee749a712eaa2))


### Bug Fixes

* **pages:** improve palette drag affordance ([b560036](https://github.com/tentaplane/tentapress/commit/b560036a13bac64bdcd40fdabbae47181646ba2b))
* **pages:** reduce drop marker jitter ([85ddf9f](https://github.com/tentaplane/tentapress/commit/85ddf9f3407b4a226c9d1bb261798c90676dd32e))
* **pages:** refine fullscreen gutters ([24e340d](https://github.com/tentaplane/tentapress/commit/24e340de72897708cf4e9a13aaf6a2c5d98e14e0))
* **pages:** restore dropzones and end marker ([ad3637e](https://github.com/tentaplane/tentapress/commit/ad3637ecbf33ac162fa253453b7419b3bd683ab8))
* **pages:** smooth drop marker behavior ([72b60ca](https://github.com/tentaplane/tentapress/commit/72b60ca55f0fa635aec38f8ce538ddb10294d288))
* **pages:** stabilize drop marker ([71ad2f6](https://github.com/tentaplane/tentapress/commit/71ad2f6d2b5b10f31bf890c512f7f224d405634d))
* **pages:** stack sticky block palette ([08454e0](https://github.com/tentaplane/tentapress/commit/08454e0b551ab52e4fad6139348bf8ff0b442db5))
* **pages:** steady dropzone hover ([c285d19](https://github.com/tentaplane/tentapress/commit/c285d19a61912e7d2f701d3c8898f73a5a6341d7))
* **pages:** tighten fullscreen editor spacing ([438030f](https://github.com/tentaplane/tentapress/commit/438030fb1a34d1cd8713c27c0891f2cc79f4f3e4))
* **setup:** unintentional commit of test plugin ([b826847](https://github.com/tentaplane/tentapress/commit/b8268477194a20ee661d5d5cd04957cb62840ad2))


### Miscellaneous Chores

* **composer:** require acme events schedule ([536e721](https://github.com/tentaplane/tentapress/commit/536e72162f7c87acb7eb6d5d239249e49675238f))
* **config:** allow acme plugin discovery ([1a8c081](https://github.com/tentaplane/tentapress/commit/1a8c0813c93e5581794b9d3bd692f976226c96e8))

## [0.9.1](https://github.com/tentaplane/tentapress/compare/v0.9.0...v0.9.1) (2026-02-01)


### Bug Fixes

* **blocks:** select reflects stored value ([4c1ed04](https://github.com/tentaplane/tentapress/commit/4c1ed043c4a164c04135594bdb66e358fe1577b5))
* **blocks:** sync select fields ([a4aa19d](https://github.com/tentaplane/tentapress/commit/a4aa19d9597f6e05494459f364983ee8a77f7cf4))
* **setup:** blocks copying from vendors not locally ([c8034f3](https://github.com/tentaplane/tentapress/commit/c8034f3ede31ff61ec1be3cd253c6877fd6c2e7a))
* **theme:** configure dev hmr origin ([436e4a6](https://github.com/tentaplane/tentapress/commit/436e4a6615d10d82b0c75609c9480afcfaa84d74))
* **theme:** load env and refresh views ([d3c77c5](https://github.com/tentaplane/tentapress/commit/d3c77c510f7043621edd15e0d2b057341e5d796d))

## [0.9.0](https://github.com/tentaplane/tentapress/compare/v0.8.2...v0.9.0) (2026-02-01)


### Features

* **setup:** default to tailwind theme ([3a67da5](https://github.com/tentaplane/tentapress/commit/3a67da5b8ffdfea884a598db87dfca659e1011f9))


### Bug Fixes

* **setup:** copy demo blocks from plugin ([00ccc47](https://github.com/tentaplane/tentapress/commit/00ccc4788f10e77d97de7f4259b5ca6b7227b945))
* **setup:** removing test installation workflow ([10f4f02](https://github.com/tentaplane/tentapress/commit/10f4f02e644c0a52b75d1dc8d7df2788853d783a))

## [0.8.2](https://github.com/tentaplane/tentapress/compare/v0.8.1...v0.8.2) (2026-02-01)


### Bug Fixes

* **setup:** delay before theme build ([f4b1198](https://github.com/tentaplane/tentapress/commit/f4b1198da60cd7dbd39c36545fb830d1e62c5cfe))
* **setup:** ensure demo block views copied ([2bee041](https://github.com/tentaplane/tentapress/commit/2bee04157bb5e68dc9b941bed4beb595060a8442))

## [0.8.1](https://github.com/tentaplane/tentapress/compare/v0.8.0...v0.8.1) (2026-02-01)


### Bug Fixes

* **setup:** build assets once after demo ([7b9a69d](https://github.com/tentaplane/tentapress/commit/7b9a69d768ffcbecaea2fba442d920e28b81b810))

## [0.8.0](https://github.com/tentaplane/tentapress/compare/v0.7.0...v0.8.0) (2026-02-01)


### Features

* **setup:** rebuild assets after demo seed ([4f279f6](https://github.com/tentaplane/tentapress/commit/4f279f6ebbf9316072d9066640e95e7e1917cc97))
* **theme:** add bold tailwind landing ([73ed24f](https://github.com/tentaplane/tentapress/commit/73ed24fec78b6e6dd936c1837a189062ca193b7b))


### Bug Fixes

* **setup:** avoid tinker for demo seed ([7daced2](https://github.com/tentaplane/tentapress/commit/7daced29d4606900efccdaf768c968f113beefec))

## [0.7.0](https://github.com/tentaplane/tentapress/compare/v0.6.0...v0.7.0) (2026-02-01)


### Features

* **setup:** offer theme deps install ([10519c4](https://github.com/tentaplane/tentapress/commit/10519c481db009a726fc138bde08d43160aa5ce9))


### Bug Fixes

* **themes:** sync only from local themes ([b491147](https://github.com/tentaplane/tentapress/commit/b491147b9aae61b5c35796ddeb2c0961fc098ffb))

## [0.6.0](https://github.com/tentaplane/tentapress/compare/v0.5.1...v0.6.0) (2026-02-01)


### Features

* **setup:** guard theme assets and offer build ([2e3c28e](https://github.com/tentaplane/tentapress/commit/2e3c28e27f3024ac90e7c7b6ff818bbdb9799fac))
* **setup:** prompt for asset build tool ([d844924](https://github.com/tentaplane/tentapress/commit/d844924d69d65d276e8a1726cf8f72e7e0e6ca38))


### Bug Fixes

* **setup:** resolve installed theme paths ([3f2ad60](https://github.com/tentaplane/tentapress/commit/3f2ad601100cca4e8861826a195137fada307eb4))
* **themes:** support vendor namespaces and copy ([8ca2318](https://github.com/tentaplane/tentapress/commit/8ca2318eb642c962a137773636a28471cca80b4c))


### Miscellaneous Chores

* **setup:** add next-step guidance ([66929a8](https://github.com/tentaplane/tentapress/commit/66929a8dc8f41992fab9ce38056c50b58353e666))

## [0.5.1](https://github.com/tentaplane/tentapress/compare/v0.5.0...v0.5.1) (2026-02-01)


### Bug Fixes

* Update base themes with generic information ([18fc71f](https://github.com/tentaplane/tentapress/commit/18fc71f1c99d35cf854b2c59dd1d144432b9d1a5))

## [0.5.0](https://github.com/tentaplane/tentapress/compare/v0.4.3...v0.5.0) (2026-01-31)


### Features

* Improved installation script ([c92e4a5](https://github.com/tentaplane/tentapress/commit/c92e4a51721abf708b56edb3008e7ea77d7a7ed0))
* **setup:** install selected theme package ([947c900](https://github.com/tentaplane/tentapress/commit/947c900373e8c53c0064ac6073836d8285dc7b38))
* **setup:** prompt for theme install ([4f6b30c](https://github.com/tentaplane/tentapress/commit/4f6b30c0043972e090bbca719a4712e4048eaaac))
* **setup:** use theme package repos ([457f4c3](https://github.com/tentaplane/tentapress/commit/457f4c3f1e3c973085d17f5ad901ea6e6567858b))
* **themes:** add composer metadata ([a5190ba](https://github.com/tentaplane/tentapress/commit/a5190ba80ec247ece7a324323a3b96e98cc09e18))

## [0.4.3](https://github.com/tentaplane/tentapress/compare/v0.4.2...v0.4.3) (2026-01-31)


### Bug Fixes

* Bumping admin shell plugin version to include new menu builder ([862ae39](https://github.com/tentaplane/tentapress/commit/862ae39de2db8a710026f74df66d058ecf50a4a0))

## [0.4.2](https://github.com/tentaplane/tentapress/compare/v0.4.1...v0.4.2) (2026-01-31)


### Bug Fixes

* use database as source for enabled plugins ([f23b108](https://github.com/tentaplane/tentapress/commit/f23b108fc469545742453bed3e66ac015dccc1fa))

## [0.4.1](https://github.com/tentaplane/tentapress/compare/v0.4.0...v0.4.1) (2026-01-31)


### Miscellaneous Chores

* initialise release please ([fbfc866](https://github.com/tentaplane/tentapress/commit/fbfc866e187f48474b566af58c0fa2e3b3349c0c))

## [0.3.1](https://github.com/tentaplane/tentapress/compare/v0.3.0...v0.3.1) (2026-01-31)


### Bug Fixes

* Ensure plugin sync is correctly documented ([3683f18](https://github.com/tentaplane/tentapress/commit/3683f184bf42ce8f135e9abe7d137ceb8f4b6f20))
* Plugins and Themes can be synced and enabled from vendor folders ([242cbbd](https://github.com/tentaplane/tentapress/commit/242cbbdae8a5f150e5896c4efb826d90fc178cf4))
* System info permissions update ([18d4c2e](https://github.com/tentaplane/tentapress/commit/18d4c2e9e0e0ac302f82248de0c58f43958b515f))
* User role capabilities table update ([ae67020](https://github.com/tentaplane/tentapress/commit/ae6702043d15dbbcb649aa925ab4c33f73dcd83a))

## [0.3.0](https://github.com/tentaplane/tentapress/compare/v0.2.0...v0.3.0) (2026-01-30)


### Features

* Following conventional commits and release please automation ([cae4420](https://github.com/tentaplane/tentapress/commit/cae44208313064324821dcb7f56c60846ddf7dbb))

## [Unreleased]
- Initial release automation setup.
- Release Please bootstrap entry.
