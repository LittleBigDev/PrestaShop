<?php
/**
 * 2007-2018 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2018 PrestaShop SA
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShopBundle\Command;

use Context;
use InvalidArgumentException;
use PrestaShop\PrestaShop\Core\Foundation\IoC\Exception;
use PrestaShopBundle\Translation\Translator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tests\PrestaShopBundle\Utils\DatabaseCreator;

/**
 * This ContainerAwareCommand command helps using DatabaseCreator when no controller (thus no container) is set into
 * context. TestDbCommand injects itself into Context and replaces the missing controller when a service is needed.
 */
class TestDbCommand extends ContainerAwareCommand
{
    private $allowedActions = array(
        'create',
        'restore',
    );

    /**
     * @var FormatterHelper
     */
    protected $formatter;

    /**
     * @var Translator
     */
    protected $translator;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    public $controller_type;

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('prestashop:test:test-db')
            ->setDescription("Manage the tests database")
            ->addArgument(
                'action',
                InputArgument::REQUIRED,
                sprintf('Action to execute (Allowed actions: %s).', implode(', ', $this->allowedActions))
            );
    }

    /**
     * Init command state
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function init(InputInterface $input, OutputInterface $output)
    {
        $this->controller_type = 'admin';
        $this->formatter       = $this->getHelper('formatter');
        $this->translator      = $this->get('translator');
        $this->input           = $input;
        $this->output          = $output;

        Context::getContext()->controller = $this;
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->init($input, $output);
        $action = $input->getArgument('action');

        try {
            $this->executeDatabaseCreatorAction($action);
        } catch (Exception $e) {
            $this->displayMessage($e->getMessage());

            return 1;
        }

        $this->displayMessage($action . ' test database action succeeded.');

        return 0;
    }

    /**
     * Execute one of the DatabaseCreator allowed actions
     *
     * @param string $action
     *  The DatabaseCreator method to be executed
     *
     * @throws Exception
     */
    protected function executeDatabaseCreatorAction($action)
    {
        if (!in_array($action, $this->allowedActions)) {
            throw new InvalidArgumentException(
                'Unknown DatabaseCreator action. It must be one of these values: '
                . implode(', ', $this->allowedActions)
            );
        }

        $method = $action . 'TestDb';
        DatabaseCreator::{$method}();
    }

    /**
     * Displays a message into provided command output
     *
     * @param string $message
     *  The message to be displayed
     *
     * @param string $type
     *  The type of message
     *  Default is "info". It can also be "comment", "question", "error"...
     */
    protected function displayMessage($message, $type = 'info')
    {
        $this->output->writeln(
            $this->formatter->formatBlock($message, $type, true)
        );
    }

    /**
     * Gets a service from the service container.
     *
     * @param string $serviceId Service identifier
     *
     * @return object The associated service
     */
    public function get($serviceId)
    {
        return $this->getContainer()->get($serviceId);
    }
}
