<?php
class PHpParserVisitor extends \PhpParser\NodeVisitorAbstract
{
    protected $_inGetSettingsMethod = false;
    protected $_classname = false;
    protected $_wrongTrlMasks;

    public function enterNode(\PhpParser\Node $node)
    {
        if ($node instanceof \PhpParser\Node\Stmt\Class_) {
            $this->_classname = (string)$node->name;
        } else if ($node instanceof \PhpParser\Node\Stmt\ClassMethod) {
            if ((string)$node->name == 'getSettings') {
                $this->_inGetSettingsMethod = true;
            }
        } else if ($this->_inGetSettingsMethod
            && ($node instanceof \PhpParser\Node\Expr\FuncCall || $node instanceof \PhpParser\Node\Expr\MethodCall)
        ) {
            if (!($node->name instanceof \PhpParser\Node\Name)) return;
            $functionName = (string)$node->name;
            if (array_key_exists($functionName, $this->_wrongTrlMasks)) {
                $this->_wrongTrlMasks[$functionName][] = array(
                    'class' => $this->_classname,
                    'line' => $node->getLine()
                );
            }
        }
    }

    public function leaveNode(\PhpParser\Node $node)
    {
        if ($this->_inGetSettingsMethod && $node instanceof \PhpParser\Node\Stmt\ClassMethod) {
            if ((string)$node->name == 'getSettings') {
                $this->_inGetSettingsMethod = false;
            }
        }
    }

    public function getWrongTrlMasks()
    {
        return $this->_wrongTrlMasks;
    }

    public function resetWrongTrlMasks()
    {
        $this->_wrongTrlMasks = array(
            'trl' => array(),
            'trlc' => array(),
            'trlp' => array(),
            'trlcp' => array(),

            'trlKwf' => array(),
            'trlcKwf' => array(),
            'trlpKwf' => array(),
            'trlcpKwf' => array(),
        );
    }
}
