.checkout
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
            3.  (must - v zavislosti od zvolenej globalnej politiky) k premennym, ktore chceme zobrazovat v dokumentacii dodame: * @Expose
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