<?php

namespace Drupal\salesforce\Commands;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\salesforce\Rest\RestClient;
use Drush\Commands\DrushCommands;
use Drush\Drush;
use Drush\Exceptions\UserAbortException;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Output\Output;

/**
 * Shared command base for Salesforce Drush commands.
 */
abstract class SalesforceCommandsBase extends DrushCommands {

  /**
   * The Salesforce client.
   *
   * @var \Drupal\salesforce\Rest\RestClient
   */
  protected $client;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $etm;

  /**
   * SalesforceCommandsBase constructor.
   *
   * @param \Drupal\salesforce\Rest\RestClient $client
   *   SF client.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $etm
   *   Entity type manager.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(RestClient $client, EntityTypeManagerInterface $etm) {
    $this->client = $client;
    $this->etm = $etm;
  }

  /**
   * Collect a salesforce object name, and set it to "object" argument.
   *
   * NB: there's no actual validation done here against Salesforce objects.
   * If there's a way to attach multiple hooks to one method, please patch this.
   */
  protected function interactObject(Input $input, Output $output, $message = 'Choose a Salesforce object name') {
    if (!$input->getArgument('object')) {
      $objects = $this->client->objects();
      if (!$answer = $this->io()->choice($message, array_combine(array_keys($objects), array_keys($objects)))) {
        throw new UserAbortException();
      }
      $input->setArgument('object', $answer);
    }
  }

  /**
   * Pass-through helper to add appropriate formatters for a query result.
   *
   * @param \Drupal\salesforce\Commands\QueryResult $query
   *   The query result.
   *
   * @return \Drupal\salesforce\Commands\QueryResult
   *   The same, unchanged query result.
   */
  protected function returnQueryResult(QueryResult $query) {
    $formatter = new QueryResultTableFormatter();
    $formatterManager = Drush::getContainer()->get('formatterManager');
    $formatterManager->addFormatter('table', $formatter);
    return $query;
  }

}
