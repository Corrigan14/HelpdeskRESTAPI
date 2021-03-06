.REQUIREMENTS
=========
php 7.1
Symfony 3.4
MYSQL 
TimeZone: UTC

git - the latest version

.FIRST STEPS TO WORK WITH A PROJECT
=========
1.  composer update
    set-up db 
        name: helpdesk
        port: 3306
        host: 127.0.0.1
        db name: root
        db pass: root
        
    check parameters.yml - db settings has to correspond to your settings
    
2. create db: php bin/console database:create
3. load fixtures: db: php bin/console database:fixtures:load
4. authentication settings 
   necessary to Generate the SSH keys:
        $ mkdir -p var/jwt # For Symfony3+, no need of the -p option
        $ openssl genrsa -out var/jwt/private.pem -aes256 4096
        $ openssl rsa -pubout -in var/jwt/private.pem -out var/jwt/public.pem
        
   write the created password to: 
        parameters.yml (jwt_key_pass_phrase)
        parameters.yml.dist (jwt_key_pass_phrase)
 
5. AVAILABLE ON: http://127.0.0.1:8000/v1/doc
  

.Drop the whole DB
=========    
php bin/console doctrine:schema:drop --full-database --force
php bin/console doctrine:schema:update --force
php bin/console doctrine:fixtures:load


.API Dokumentácia
=========

API Dokumentácia

Pouzivanie: NelmioAPIDoc. 
Zdroj: 	    http://symfony.com/doc/current/bundles/NelmioApiDocBundle/the-	apidoc-annotation.html

Dokumentacne pravidla
1. Ak metoda vracia: Entitu, Objekt, Premennu … priklad tohto elementu uvedieme v uvode dokumentacie. 

 	POZN. Na ziskanie ukazky respons-u mozeme vyuzit SANDBOX 

	PRIKLAD: 
    * ### Response ###
    *
    *     {
    *       "data": [
    *                   {
    *                         "id": "1",
    *                         "email": "admin@admin.sk",
    *                         "username": "admin",
    *                         "_links": {
    *                             "self": "/users/1"
    *                         }
    *                 }
    *          ],
    *       "_links": {
    *             "self": "/users?page=1&fields=id,email,username",
    *             "first": "/users?page=1&fields=id,email,username",
    *             "prev": false,
    *             "next": "/users?page=2&fields=id,email,username",
    *             "last": "/users?page=3&fields=id,email,username"
    *       },
    *       "total": 22,
    *       "page": 1,
    *       "numberOfPages": 3
    *     }

2. Samotny @ApiDoc obsahuje (potrebne) elementy (v tomto poradi):

    DESCRIPTION 
        POPIS POUZITIA:popis fungovania metody
    
    INPUT 
        POPIS POUZITIA: definovanie triedy Entity, ktora ma byt vytvorena/upravena. Definuje sa iba pre metody POST, PUT, PATCH
        DEFINUJE SA: Samotna trieda
        DOLEZITE: pre spravne zobrazenie jednotlivych parametrov v dokumentacii (type, required, format, description) je potrebne spravne definovanie parametrov v samotnej entite. Vyuzivame na to JMS SERIALIZER:
            1. (optional) k samotnej triede dodame politiku zobrazenia parametrov podla toho, ci chceme zobrazit vsetky alebo ziadne parametre: @ExclusionPolicy("all/none")
            2. (must - v zavislosti od zvolenej globalnej politiky) k premennym, ktore nechceme zobrazovat v dokumentacii (ich hodnoty sa nastavuju systemovo) dodame:  @Exclude
            3. (must - v zavislosti od zvolenej globalnej politiky) k premennym, ktore chceme zobrazovat v dokumentacii dodame: * @Expose
            4. (must) pre definovanie Typ-u musime ku kazdej premennej doplnit @Assert\Type("type")
            5. (must) pre definovanie Format-u (pravidiel ako napr. ze ide o Email, ze pole nesmie byt prazdne..) musime tieto pravidla definovat jednotlivo pre premenne cez Asserts: * @Assert\NotBlank()
        PRIKLAD:
        *  input={"class"="API\CoreBundle\Entity\User"},

    REQUIREMENT
        POPIS POUZITIA: parametre, ktore sa povinne nachadzaju v URL adrese (napr ID)
        DEFINUJU SA:
            name
            dataType
            requirement
            description
        PRIKLAD:
        *  requirements={
        *     {
        *       "name"="id",
        *       "dataType"="integer",
        *       "requirement"="\d+",
        *       "description"="The id of processed object"
        *     }
        *  },
    
    FILTERS
        POPIS POUZITIA:  parametre, ktore su zadavane v ramci URL za ? - vzdy nepovinne
        DEFINUJE SA: 
            name
            description 
            mozu ist aj dalsie informacie, podla potreby
        PRIKLAD:
        *  filters={
        *     {
        *       "name"="fields",
        *       "parameters"="username|email|password...",
        *       "description"="Custom fields to get only selected data"
        *     },
        *  },
        
    PARAMETERS
        POPIS POUZITIA:  parametre, ktore su zadavane pri odosielani URL
        DEFINUJE SA:
            name
            dataType
            required
            format (GET/POST)
            description
        POZNAMKA: vyuzivat tuto formu definovania vstupnych parametroch budeme iba v pripade, ze nevieme pouzit definovanie parametrov prostrednictvom Triedy Entity, ktore nam  umoznuje INPUT (pozri vyssie)
        PRIKLAD:
        *  parameters={
        *      {"name"="username", "dataType"="string", "required"=true, "format"="POST", "description"="username for
        *      login purposes"},
        *      {"name"="password", "dataType"="string", "required"=true, "format"="POST", "description"="password for
        *      login purposes"}
        *  },
        
    HEADERS
        POPIS POUZITIA: hlavicku je nutne definovat pre kazdu metodu, ktoru moze pouzivat iba Autorizovany pouzivatel. Autorizovany pouzivatel ma JWT Token, ktory musi pri zavolani kazdej metody manualne vlozit do hlavicky ako hodnotu paramtera Authorization, spolu s prefixom Bearer:
        DEFINUJE SA:
            name
            description
            required
        PRIKLAD:
        *  headers={
        *     {
        *       "name"="Authorization",
        *       "required"=true,
        *       "description"="Bearer {JWT Token}"
        *     }
        *  },
        
    OUTPUT (RETURN)
        POPIS POUZITIA:  definovanie triedy Entity, ktora ma byt vratena v ramci uspesne vykonaneho Respons-u.
        DEFINUJE SA: Samotna trieda
        PRIKLAD:
        *  output={"class"="API\CoreBundle\Entity\User"},
    
    STATUS CODES
        POPIS POUZITIA: 
        DEFINUJE SA: V kazdej metode, jednotlive kody zavisia od funkcie danej metody 
        DOLEZITE: 
            Popisy pouzitia najcastejsie vyuzivanych Status kodov sa nachadzaju v StatusCodeHelper-i (adresar: Services). 
            Navrhujem aby sme kazdy pouzity kod/spravu zapisali do tejto triedy. Zdroj a popis jednotlivych kodov:  http://www.restapitutorial.com/httpstatuscodes.html
            Do dokumentacie budeme vkladat k jednotlivym kodom popis korespondujuci s popisom v StatusCodeHelper-i
            Pri tvorbe Json vystupu pouzijeme kod a spravu z tohto helpera: 
                StatusCodeHelper::INCORRECT_CREDENTIAL_MESSAGE
                StatusCodeHelper::INCORRECT_CREDENTIAL_CODE
        PRIKLAD:
        *  statusCodes={
        *      201="The entity was successfully created",
        *      409="Invalid parameters",
        *  },
        
.Tvorba novej Entity
=========
1. Entita
    - vytvorenie Entity
    - vyuzivame
        use JMS\Serializer\Annotation\ReadOnly;
        use JMS\Serializer\Annotation\Exclude;
    - kazdy parameter, ktory sa nema zobrazovat pri vypisovani entity musi mat nastaveny prefix @Exclude()
    - kazdy parameter, ktory sa ma zobrazovat pri vypisovani entity, ale jeho hodnota sa nezadava (napr. ID) 
      musi mat nastaveny prefix @ReadOnly()

2. Fixtures implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
    - use:
        use Doctrine\Common\DataFixtures\FixtureInterface;
        use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
        use Doctrine\Common\Persistence\ObjectManager;
        use Symfony\Component\DependencyInjection\ContainerAwareInterface;
        use Symfony\Component\DependencyInjection\ContainerInterface;

3. Controller
    - vytvorenie prazdneho Controllera
    - kazdy Controller: extends ApiBaseController implements ControllerInterface
                        use Nelmio\ApiDocBundle\Annotation\ApiDoc;

4. Routy (Resources/config/routing/entityName.yml)
    - vytvorenie entityName.yml suboru
    - kazdu skupinu rout musime zaregistrovat v routing.yml
    - kazda entita bude mat zakladnu skupinu rout (pozri user.yml)
    - pri importovanych cestach netreba pouzivat prefix pretoze tam da na konci kazdej cesty lomitko
    
5. Testy
    - pre testy vyuzivame vlastnu testovaciu databazu s fixtures:
                php bin/console doctrine:schema:update --force  --env=test
                php bin/console doctrine:fixtures:load --env=test
    - kazdy ControllerTest:  extends ApiTestCase, definuje: const BASE_URL (napr. '/api/v1/users')
    - Api TestCase implementuje ControllerTestInterface, ktory urcuje mnimalne metody pre testovanie zakladnych requestov
    - ApiTestCase automaticky testuje zakladne GET, POST, PUT, PATCH, DELETE actions (pozri ApiTestCase dokumentaciu), 
      musime vsak pripravit:
                            url (metoda: getBaseUrl)
                            testovacie data (metody: returnUpdateTestData, returnPostTestData)
                            entity (findOneEntity, createEntity, removeTestEntity)
    - kazdu metodu v ControllerTest mozeme rozsirit o testy pre specificke funkcie 
      ako napr. testovanie vkladania nespravnych udajov (napr. email nie je validny)
      Zvycajne pojde o rozsirenie:
          /**
          *  POST SINGLE - errors
          */
          public function testPostSingleErrors()
          {
              parent::testPostSingleErrors();
      
              // Try to create Entity with invalid parameter ... (... is required) [code 409]
          }
      
          /**
           *  UPDATE SINGLE - errors
           */
          public function testUpdateSingleErrors()
          {
              parent::testUpdateSingleErrors();
      
              // Try to update Entity with not invalid parameter ... (... has to be uniqe) [code 409]
          }

6. Controller
    - upravime dokumentaciu pre jednotlive metody
    - doprogramujeme uz odtestovane metody tak, aby vsetky testy presli spravne (min CRUD): phpunit
    
7. Service - vytvorenie Service (Services) 
    - Ak sa jedna o beznu Entitu ako napr. Company ci Tag, Sevice moze: extends ApiBaseService(),
     alebo mozeme priamo vyuzivat metody ApiBaseServicu:
            - metody na ziskanie Zoznamu entit 
              (v metode listAction: getEntitiesResponse($entityRepository napr. $this->getDoctrine()->getRepository('ApiCoreBundle:Company'), $page, $routeName napr. 'company_list', $options = [])) 
            - metody na ziskanie jednej entity (v metode getAction: getEntityResponse($entity, $entityName napr. 'company')) 
    - registracia Service (Resources/config/services.yml)
    
8. Repository 
    - tento automaticky vygeneruje Doctrine pri vytvoreni entity. AK nie, je potrebne ho v entite zaregistrovat 
    - ak vyuzivame ApiBaseService potrebujeme, aby nas repozitar 
            extends EntityRepository implements RepositoryInterface
    
9. Security 
    - doplnenie VoterOptions.php - obsahuje konstanty s akciami, pre ktore su potrebne pravidla vykonavania 
    - vytvorenie EntityNameVoter.php: extends ApiBaseVoter implements VoterInterface 
    - registracia EntityNameVoter (Resources/config/services.yml)
    