IgsemAPIBundle
==============

This bundle Provides simple Abstract classes for easing the API Development. 
A Base Controller class to generate a skeleton CRUD with NelmioApiDoc already configured.
Also an APIBase test which runs automatically some CRUD tests for your API
..minimum configuration required for a pretty good test coverage. 
We also have a LoginTrait for the tests to be able to login into our API via a token.

Installation:
Just install the requirements and register the bundles in your AppKernel

new Nelmio\ApiDocBundle\NelmioApiDocBundle() ,

new JMS\SerializerBundle\JMSSerializerBundle(),

new \Igsem\APIBundle\IgsemAPIBundle() ,
