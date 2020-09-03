<?php

namespace Symfony\Permission\Adapters;

use Casbin\Model\Model;
use Casbin\Persist\Adapter;
use Casbin\Persist\AdapterHelper;
use Doctrine\DBAL\DriverManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DatabaseAdapter extends AbstractController implements Adapter
{
    use AdapterHelper;

    /**
     * Rules eloquent model
     *
     * @var Rule
     */
    protected $connection;

    /**
     * the DatabaseAdapter constructor.
     *
     * @param string $url
     */
    public function __construct(string $url)
    {
        $conn = DriverManager::getConnection([
            'url' => $url
        ]);

        $this->connection = $conn->createQueryBuilder();
    }

    /**
     * savePolicyLine function.
     *
     * @param string $ptype
     * @param array  $rule
     */
    public function savePolicyLine(string $ptype, array $rule): void
    {
        $col['ptype'] = $ptype;
        foreach ($rule as $key => $value) {
            $col['v' . strval($key)] = $value;
        }

        $result = $this->connection->insert('casbin_rules')
            ->values([
                'ptype' => '?',
                'v0' => '?',
                'v1' => '?',
                'v2' => '?',
                'v3' => '?',
                'v4' => '?',
                'v5' => '?',
            ])
            ->setParameter(0, isset($col['ptype']) ? $col['ptype'] : '')
            ->setParameter(1, isset($col['v0']) ? $col['v0'] : '')
            ->setParameter(2, isset($col['v1']) ? $col['v1'] : '')
            ->setParameter(3, isset($col['v2']) ? $col['v2'] : '')
            ->setParameter(4, isset($col['v3']) ? $col['v3'] : '')
            ->setParameter(5, isset($col['v4']) ? $col['v4'] : '')
            ->setParameter(6, isset($col['v5']) ? $col['v5'] : '');

        $result = $result->execute();
    }

    /**
     * loads all policy rules from the storage.
     *
     * @param Model $model
     */
    public function loadPolicy(Model $model): void
    {
        $rows = $this->connection->select('ptype', 'v0', 'v1', 'v2', 'v3', ' v4', 'v5')->from('casbin_rules');
        $rows = $rows->execute()->fetchAll();

        foreach ($rows as $row) {
            $line = implode(', ', array_filter($row, function ($val) {
                return '' != $val && !is_null($val);
            }));
            $this->loadPolicyLine(trim($line), $model);
        }
    }

    /**
     * saves all policy rules to the storage.
     *
     * @param Model $model
     */
    public function savePolicy(Model $model): void
    {
        foreach ($model['p'] as $ptype => $ast) {
            foreach ($ast->policy as $rule) {
                $this->savePolicyLine($ptype, $rule);
            }
        }

        foreach ($model['g'] as $ptype => $ast) {
            foreach ($ast->policy as $rule) {
                $this->savePolicyLine($ptype, $rule);
            }
        }
    }

    /**
     * adds a policy rule to the storage.
     * This is part of the Auto-Save feature.
     *
     * @param string $sec
     * @param string $ptype
     * @param array  $rule
     */
    public function addPolicy(string $sec, string $ptype, array $rule): void
    {
        $this->savePolicyLine($ptype, $rule);
    }

    /**
     * This is part of the Auto-Save feature.
     *
     * @param string $sec
     * @param string $ptype
     * @param array  $rule
     */
    public function removePolicy(string $sec, string $ptype, array $rule): void
    {
        $instance = $this->connection
            ->delete('casbin_rules')
            ->where('ptype = ?')
            ->setParameter(0, $ptype);

        $i = 1;
        foreach ($rule as $key => $value) {
            $instance->andwhere("v{$key} = ?")
                ->setParameter($i++, $value);
        }

        $instance->execute();
    }

    /**
     * RemoveFilteredPolicy removes policy rules that match the filter from the storage.
     * This is part of the Auto-Save feature.
     *
     * @param string $sec
     * @param string $ptype
     * @param int    $fieldIndex
     * @param string ...$fieldValues
     */
    public function removeFilteredPolicy(string $sec, string $ptype, int $fieldIndex, string ...$fieldValues): void
    {
        $instance = $this->connection
            ->delete('casbin_rules')
            ->where('ptype = ?')
            ->setParameter(0, $ptype);

        $i = 1;
        foreach (range(0, 5) as $value) {
            if ($fieldIndex <= $value && $value < $fieldIndex + count($fieldValues)) {
                if ('' != $fieldValues[$value - $fieldIndex]) {
                    $instance->andwhere("v{$value} = ?")
                        ->setParameter($i++, $fieldValues[$value - $fieldIndex]);
                }
            }
        }

        $instance->execute();
    }
}
