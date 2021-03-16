<?php

namespace Commands\Schemas;

use Helpers\HubspotClientHelper;
use HubSpot\Client\Crm\Schemas\Model\ObjectTypeDefinitionPatch;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UpdateCommand extends SchemasCommand
{
    protected static $defaultName = 'schemas:update';

    protected function configure()
    {
        $this->setDescription('Update an object`s schema by objectTypeId (Fully qualified name or object type ID for the target schema).');
        $this->addObjectTypeIdToCommand();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $hubspot = HubspotClientHelper::createFactory();
        
        $objectTypeId = $input->getArgument('objectTypeId');
        $response = $hubspot->crm()->schemas()->CoreApi()->getById($objectTypeId);
        $labels = $response->getLabels();
        
        $singularLabel = $io->ask(
            'Enter a new singular label for the schema',
            $labels->getSingular(),
            $this->getNotEmptyValidator()
        );
        
        $pluralLabel = $io->ask(
            'Enter a new plural label for the schema',
            $labels->getPlural(),
            $this->getNotEmptyValidator()
        );

        $io->writeln("Updating an object with objectTypeId: {$objectTypeId}");
        
        
        $labels->setSingular($singularLabel);
        $labels->setPlural($pluralLabel);
        $schema = new ObjectTypeDefinitionPatch();
        
        $schema->setLabels($labels);
        $schema->setRequiredProperties($response->getRequiredProperties());
        
        
        $updateResponse = $hubspot->crm()->schemas()->CoreApi()->update($objectTypeId, $schema);
        
        $io->info($updateResponse);

        return SchemasCommand::SUCCESS;
    }

}

