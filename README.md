# INT20H2026Webdogs
Тестове завдання 

Цей проєкт створено як розв'язання бізнес-проблеми для сервісу доставки дронами в штаті Нью-Йорк. Основна мета — автоматизувати розрахунок Composite Sales Tax для кожного замовлення на основі географічних координат (latitude/longitude) точки доставки.

Система дозволяє імпортувати дані з CSV файлу, створювати замовлення та переглядати результати в вигляді таблиці податкових нарахувань, маючи можливість застосовувати фільтри.


# Технологічний стек:

Backend: PHP (чистий REST API) з використанням паттернів Controller-Service-Gateway.
Database: MySQL (зберігання замовлень та податкових ставок).
Auth: JWT (JSON Web Token) для безпечного доступу до адмін-панелі.
Frontend: Vanilla JavaScript (модульна архітектура з компонентами).


# Архітектура та прийняті рішення

Згідно з порадами до завдання, основний фокус було зроблено на бізнес-логіці:

Гео-кодування та Юрисдикції: Оскільки податок у штаті Нью-Йорк залежить від конкретної адреси (штат + округ + місто + спеціальні зони) , у системі реалізовано сервіс JurisdictionService, який визначає ставку на основі вхідних координат.

Розділення відповідальності:
Gateways: Ізольована робота з SQL-запитами.
Services: Розрахунок податкових ставок (TaxCalculatorService) та обробка CSV файлів.
Validators: Жорстка перевірка вхідних даних (координати, суми) перед збереженням.


# Функціонал

Імпорт CSV: Завантаження великих обсягів даних замовлень.
Manual Create: Створення поодиноких замовлень з миттєвим розрахунком податку.
Розширений список замовлень: Таблиця з пагінацією та фільтрами.
Tax Breakdown: Деталізація кожного податку: state_rate, county_rate, city_rate, та special_rates.


# Вимоги

PHP 8.x
MySQL 8.x
Composer
Веб-сервер (Apache/Nginx або Open Server)


# Налаштування

1. Клонуйте репозиторій:
git clone https://github.com/Busenko/INT20H2026Webdogs.git

Перемістіть вміст папки INT20H2026Webdogs у нову папку Webdogs.
Розмістіть папку Webdogs у директорію вашого локального сервера (наприклад, для Open Server Panel: home/Webdogs).

2. Конфігурація Open Server (.osp)
Відкрийте папку .osp у корені проєкту.
Перевірте або створіть файл project.ini з наступним вмістом:
    [Webdogs]
    php_engine = PHP-8.4
Запустіть або перезапустіть Open Server Panel.


3. Встановіть PHP залежності:
Для роботи з JWT та файлами конфігурації .env необхідно встановити бібліотеки через Composer
    cd private
    composer install

4. Налаштуйте базу даних:
Створіть базу даних (наприклад, order_db) у MySQL.
Імпортуйте структуру з файлу base/order_db.sql.
Налаштуйте підключення у файлі private/.env:
    DB_HOST=MySQL-8.4          хост
    DB_NAME=order_db           назва бази
    DB_USER=root               користувач
    DB_PASS=                   пароль (порожній або ваш)



5. Налаштування Frontend
Перейдіть у файл frontend/src/config/appConfig.js.
Оновіть параметр API_URL, вказавши адресу, на якій працює ваш PHP-сервер (наприклад, домен в Open Server):
    export const CONFIG = {
        // Вкажіть URL вашого локального бекенду
        API_URL: 'http://webdogs', 
        ENDPOINTS: {
            LOGIN: '/login',
            ORDERS: '/orders',
            JURISDICTIONS: '/orders/jurisdictions',
            IMPORT: '/orders/import'
        },
        STORAGE_KEYS: {
            TOKEN: 'jwt_token'
        }
    };

Коли backend та база даних налаштовані та запущені!
6. Запуск файлу frontend/index.html через VS Code - Live Server 
Оскільки додаток використовує ES-модулі (import/export), відкриття файлу index.html просто подвійним кліком у браузері не працюватиме через політику CORS.

Інструкція для Visual Studio Code:

Відкрийте корінь проєкту у VS Code.
Переконайтеся, що у вас встановлено розширення Live Server.
Натисніть правою кнопкою миші на файл frontend/index.html.
Оберіть "Open with Live Server".
Додаток відкриється за адресою http://127.0.0.1:5500/frontend/index.html.

7. Уведіть логін та пароль зазначені у файлі private/.env:
    ADMIN_Login="webdogs"
    ADMIN_Pass="0123webdogs-start"


# Джерела даних та інструменти

- Географічні межі (Civil Boundaries)
Дані про кордони округів та міст штату Нью-Йорк взяті з офіційного геопорталу

Джерело: https://gis.ny.gov/civil-boundaries
Використання: Ці дані стали основою для визначення юрисдикції за координатами (Latitude/Longitude).

- Конвертація та обробка геоданих
Оскільки початкові дані часто надаються у форматі Shapefile (.shp) та в метричній системі координат, було використано інструмент Mapshaper:
Ресурс: https://mapshaper.org/

Команда для Mapshaper (конвертація та проектування):
    -proj wgs84                Ця команда переводить вхідні дані у десяткові градуси (широту та довготу).
    -o format=geojson          Конвертує ваші географічні об'єкти (кордони округів чи міст) у текстовий формат JSON.


# Податкові ставки та правила розрахунку

Логіка розрахунку composite_tax_rate базується на офіційних звітах Департаменту оподаткування та фінансів штату Нью-Йорк (NY State Department of Taxation and Finance)

Джерело https://www.tax.ny.gov/pdf/publications/sales/pub718.pdf

Початковим етапом є підготовка нормативної бази в сервісі TaxSeedService.php, який трансформує дані з офіційного звіту Департаменту оподаткування та фінансів штату Нью-Йорк (файл ny_taxes_2025.json) у структуровані правила. Під час цього процесу метод mapJsonToTaxData проводить декомпозицію загальної ставки: він виділяє базову державну частку в 4%, ідентифікує приналежність до спеціальних зон MCTD за допомогою списку $mctdZones (додаючи ставку 0.375%) та розраховує місцевий податок як залишок від загальної суми. Це дозволяє системі не просто зберігати цифри, а розуміти структуру податку для кожної юрисдикції, що відповідає публікації 718 податкової служби.

Операційна частина логіки активується в OrderCreationService.php, коли система отримує координати замовлення. Процес починається з виклику JurisdictionService.php, який завантажує просторові дані з Counties.json. Для забезпечення швидкодії сервіс спочатку проводить перевірку через calculateBBox (Bounding Box), щоб відсіяти точки, які завідомо не належать до округу, і лише після цього запускає складний математичний алгоритм isPointInPolygon. Якщо координати потрапляють у межі полігону, сервіс повертає точну назву округу, забезпечуючи перехід від географічної точки до юридичної назви юрисдикції.

Після визначення назви юрисдикції в справу вступає TaxCalculatorService.php, який через TaxGateway.php шукає відповідні ставки в базі даних. Метод calculate проводить нормалізацію назв (наприклад, прирівнюючи різні райони Нью-Йорка до єдиного об'єкта New York City) та повертає фінальні суми tax_amount та total_amount. Цей сервіс використовує статичний кеш $taxCache, щоб при масовому імпорту через CsvImportService.php система не перевантажувала базу даних повторними запитами для одних і тих самих округів.

Завершальний етап бізнес-логіки полягає у фізичній прив'язці податку до замовлення в OrderCreationService.php. Якщо податкові дані успішно знайдені, створюється об'єкт Order з ідентифікатором id_tax, що створює зв'язок (Foreign Key) між таблицею замовлень та таблицею податкових правил. Проте, якщо замовлення має координати за межами полігонів Нью-Йорка, JurisdictionService повертає null. У такому випадку система, дотримуючись принципу податкового зв'язку (Nexus), припиняє нарахування: TaxCalculatorService повертає порожній результат, а замовлення створюється з нульовим податком та без прив'язки до податкової ставки, оскільки податкові повноваження штату Нью-Йорк закінчуються на його географічній межі.


# Структура проєкту

│   .gitignore
│   .htaccess
│   index.php
│   README.md
│
├───.osp
│       project.ini
│
├───base
│       order_db.sql
│
├───frontend
│   │   index.html
│   │
│   └───src
│       │   app.js
│       │   main.js
│       │
│       ├───assets
│       │       style.css
│       │
│       ├───components
│       │       Filters.js
│       │       Header.js
│       │       ImportForm.js
│       │       ManualForm.js
│       │       Pagination.js
│       │       Table.js
│       │       Tabs.js
│       │       TaxDetailsModal.js
│       │
│       ├───config
│       │       appConfig.js
│       │
│       ├───services
│       │       serviceAdmin.js
│       │       serviceAuth.js
│       │       serviceHTTP.js
│       │       serviceOrder.js
│       │
│       ├───utils
│       │       helpers.js
│       │
│       └───views
│               admin.js
│               login.js
│
├───private
│   │   .env
│   │   composer.json
│   │   composer.lock
│   │
│   ├───App
│   │   ├───Controllers
│   │   │       LoginController.php
│   │   │       OrderController.php
│   │   │
│   │   ├───Core
│   │   │       BaseController.php
│   │   │       BaseGateway.php
│   │   │       BaseValidator.php
│   │   │       Database.php
│   │   │       ErrorHandler.php
│   │   │       Router.php
│   │   │       ServiceFactory.php
│   │   │
│   │   ├───Gateways
│   │   │       AdminGateway.php
│   │   │       OrderGateway.php
│   │   │       TaxGateway.php
│   │   │
│   │   ├───Middleware
│   │   │       AuthMiddleware.php
│   │   │
│   │   ├───Models
│   │   │       Admin.php
│   │   │       Order.php
│   │   │       Tax.php
│   │   │
│   │   ├───Resurses
│   │   │       BetterMe Test-Input.csv
│   │   │       Counties.json
│   │   │       ny_taxes_2025.json
│   │   │
│   │   ├───Security
│   │   │       PasswordHasher.php
│   │   │       TokenGenerator.php
│   │   │
│   │   ├───Services
│   │   │       CsvImportService.php
│   │   │       JurisdictionService.php
│   │   │       LoginService.php
│   │   │       OrderCreationService.php
│   │   │       OrderFilterService.php
│   │   │       TaxCalculatorService.php
│   │   │       TaxSeedService.php
│   │   │
│   │   └───Validators
│   │           AdminValidator.php
│   │           OrderValidator.php
│   │           TaxValidator.php
│   │
│   └───vendor
│       │   autoload.php
│       │
│       ├───bin
│       ├───composer
│       │       autoload_classmap.php
│       │       autoload_files.php
│       │       autoload_namespaces.php
│       │       autoload_psr4.php
│       │       autoload_real.php
│       │       autoload_static.php
│       │       ClassLoader.php
│       │       installed.json
│       │       installed.php
│       │       InstalledVersions.php
│       │       LICENSE
│       │       platform_check.php
│       │
│       ├───firebase
│       │   └───php-jwt
│       │       │   CHANGELOG.md
│       │       │   composer.json
│       │       │   LICENSE
│       │       │   README.md
│       │       │
│       │       └───src
│       │               BeforeValidException.php
│       │               CachedKeySet.php
│       │               ExpiredException.php
│       │               JWK.php
│       │               JWT.php
│       │               JWTExceptionWithPayloadInterface.php
│       │               Key.php
│       │               SignatureInvalidException.php
│       │
│       ├───graham-campbell
│       │   └───result-type
│       │       │   composer.json
│       │       │   LICENSE
│       │       │
│       │       └───src
│       │               Error.php
│       │               Result.php
│       │               Success.php
│       │
│       ├───phpoption
│       │   └───phpoption
│       │       │   composer.json
│       │       │   LICENSE
│       │       │
│       │       └───src
│       │           └───PhpOption
│       │                   LazyOption.php
│       │                   None.php
│       │                   Option.php
│       │                   Some.php
│       │
│       ├───symfony
│       │   ├───polyfill-ctype
│       │   │       bootstrap.php
│       │   │       bootstrap80.php
│       │   │       composer.json
│       │   │       Ctype.php
│       │   │       LICENSE
│       │   │       README.md
│       │   │
│       │   ├───polyfill-mbstring
│       │   │   │   bootstrap.php
│       │   │   │   bootstrap80.php
│       │   │   │   composer.json
│       │   │   │   LICENSE
│       │   │   │   Mbstring.php
│       │   │   │   README.md
│       │   │   │
│       │   │   └───Resources
│       │   │       └───unidata
│       │   │               caseFolding.php
│       │   │               lowerCase.php
│       │   │               titleCaseRegexp.php
│       │   │               upperCase.php
│       │   │
│       │   └───polyfill-php80
│       │       │   bootstrap.php
│       │       │   composer.json
│       │       │   LICENSE
│       │       │   Php80.php
│       │       │   PhpToken.php
│       │       │   README.md
│       │       │
│       │       └───Resources
│       │           └───stubs
│       │                   Attribute.php
│       │                   PhpToken.php
│       │                   Stringable.php
│       │                   UnhandledMatchError.php
│       │                   ValueError.php
│       │
│       └───vlucas
│           └───phpdotenv
│               │   composer.json
│               │   LICENSE
│               │
│               └───src
│                   │   Dotenv.php
│                   │   Validator.php
│                   │
│                   ├───Exception
│                   │       ExceptionInterface.php
│                   │       InvalidEncodingException.php
│                   │       InvalidFileException.php
│                   │       InvalidPathException.php
│                   │       ValidationException.php
│                   │
│                   ├───Loader
│                   │       Loader.php
│                   │       LoaderInterface.php
│                   │       Resolver.php
│                   │
│                   ├───Parser
│                   │       Entry.php
│                   │       EntryParser.php
│                   │       Lexer.php
│                   │       Lines.php
│                   │       Parser.php
│                   │       ParserInterface.php
│                   │       Value.php
│                   │
│                   ├───Repository
│                   │   │   AdapterRepository.php
│                   │   │   RepositoryBuilder.php
│                   │   │   RepositoryInterface.php
│                   │   │
│                   │   └───Adapter
│                   │           AdapterInterface.php
│                   │           ApacheAdapter.php
│                   │           ArrayAdapter.php
│                   │           EnvConstAdapter.php
│                   │           GuardedWriter.php
│                   │           ImmutableWriter.php
│                   │           MultiReader.php
│                   │           MultiWriter.php
│                   │           PutenvAdapter.php
│                   │           ReaderInterface.php
│                   │           ReplacingWriter.php
│                   │           ServerConstAdapter.php
│                   │           WriterInterface.php
│                   │
│                   ├───Store
│                   │   │   FileStore.php
│                   │   │   StoreBuilder.php
│                   │   │   StoreInterface.php
│                   │   │   StringStore.php
│                   │   │
│                   │   └───File
│                   │           Paths.php
│                   │           Reader.php
│                   │
│                   └───Util
│                           Regex.php
│                           Str.php
│
└───test
        test_admin.php
        test_import.php
        test_sync.php




