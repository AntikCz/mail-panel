<?php

namespace Nextras\MailPanel;

use Nette\Http\Request;
use Nette\Object;
use Tracy\IBarPanel;
use Latte;


/**
 * Extension for Nette debugger bar which shows sent emails
 *
 * @author Jan Drábek
 * @author Jan Marek
 * @copyright New BSD
 */
class MailPanel extends Object implements IBarPanel
{
	/** @const int */
	const DEFAULT_COUNT = 5;

	/** @var Request */
	private $request;

	/** @var SessionMailer */
	private $mailer;

	/** @var int */
	private $messagesLimit;

	/** @var string|NULL */
	private $tempDir;


	public function __construct($tempDir, Request $request, IMailer $mailer, $messagesLimit = self::DEFAULT_COUNT)
	{
		$this->request = $request;
		$this->mailer = $mailer;
		$this->messagesLimit = $messagesLimit;
		$this->tempDir = $tempDir;

		$query = $request->getQuery("mail-panel");

		if ($query === 'delete') {
			$this->handleDeleteAll();
		} elseif (is_numeric($query)) {
			$this->handleDelete($query);
		}
	}


	/**
	 * Returns panel ID
	 * @return string
	 */
	public function getId()
	{
		return __CLASS__;
	}


	/**
	 * Renders HTML code for custom tab
	 * @return string
	 */
	public function getTab()
	{
		$count = $this->mailer->getMessageCount();

		return '<span title="Mail Panel">' .
			'<svg viewBox="0 0 16 16">' .
  			'	<rect x="0" y="2" width="16" height="11" rx="1" ry="1" fill="#588ac8"/>' .
  			'	<rect x="1" y="3" width="14" height="9" fill="#eef3f8"/>' .
  			'	<rect x="2" y="4" width="12" height="7" fill="#dcebfe"/>' .
  			'	<path d="M 2 11 l 4 -4 q 2 -2 4 0 l 4 4" stroke="#bbccdd" fill="none"/>' .
  			'	<path d="M 2 4 l 4 4 q 2 2 4 0 l 4 -4" stroke="#85aae2" fill="#dee8f7"/>' .
			'</svg>' .
			'<span class="tracy-label">' . $count . ' sent email' . ($count === 1 ? '' : 's') . '</span></span>';
	}


	/**
	 * Show content of panel
	 * @return string
	 */
	public function getPanel()
	{
		$latte = new Latte\Engine;
		$latte->setTempDirectory($this->tempDir);

		return $latte->renderToString(__DIR__ . '/MailPanel.latte', array(
			'baseUrl'  => $this->request->getUrl()->getBaseUrl(),
			'messages' => $this->mailer->getMessages($this->messagesLimit),
		));
	}


	private function returnBack()
	{
		header('Location: ' . $this->request->getReferer());
		exit;
	}


	private function handleDeleteAll()
	{
		$this->mailer->clear();
		$this->returnBack();
	}


	private function handleDelete($id)
	{
		$this->mailer->deleteByIndex($id);
		$this->returnBack();
	}

}
