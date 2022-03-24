<?php

namespace backend\modules\content\models;

use Exception;
use yii\db\ActiveRecord;
use yii\db\StaleObjectException;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "content_closure".
 *
 * @property int $anc Родитель
 * @property int $dsc Потомок
 * @property int $lvl Уровень
 */
class ContentClosure extends ActiveRecord
{
	/**
	 * {@inheritdoc}
	 */
	public static function tableName(): string
	{
		return 'content_closure';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(): array
	{
		return [
			[['anc', 'dsc', 'lvl'], 'required'],
			[['anc', 'dsc', 'lvl'], 'default', 'value' => 0],
			[['anc', 'dsc', 'lvl'], 'integer'],
			[['anc', 'dsc', 'lvl'], 'unique', 'targetAttribute' => ['anc', 'dsc', 'lvl']],
			[['dsc'], 'exist', 'skipOnError' => true, 'targetClass' => Content::class, 'targetAttribute' => ['dsc' => 'id']],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(): array
	{
		return [
			'anc' => 'Родитель',
			'dsc' => 'Потомок',
			'lvl' => 'Уровень',
		];
	}

	/**
	 * @param array $from
	 * @param array $to
	 * @return array
	 */
	public static function map(array $from, array $to): array
	{
		$data = [];
		if ($from == $to) {
			$data = $from;
		} else {
			foreach ($from as $fromNode) {
				$fromDsc = $fromNode['dsc'];
				$fromLvl = $fromNode['lvl'];


				if (!empty($fromDsc)) {
					foreach ($to as $toNode) {
						$toAnc = $toNode['anc'];
						$toLvl = $toNode['lvl'];
						if (!empty($toAnc) && $toAnc != $fromDsc) {
							$data[] = [
								'anc' => $toAnc,
								'dsc' => $fromDsc,
								'lvl' => $fromLvl + $toLvl + 1,
							];
						}
					}
				}
			}
		}
		return $data;
	}

	/**
	 * @param int $id
	 * @param bool $return
	 * @return mixed|void
	 */
	public static function node(int $id, bool $return = false)
	{
		$node = ContentClosure::find()->where([
			'anc' => $id,
			'dsc' => $id,
			'lvl' => 0,
		])->one();
		if (empty($node)) {
			$node = new ContentClosure([
				'anc' => $id,
				'dsc' => $id,
				'lvl' => 0,
			]);
			if (!$node->save()) {
				print_r($node->errors);
			}
		}
		if ($return) {
			return $node->toArray();
		}
	}

	/**
	 * @throws Exception
	 */
	public static function __insert__(int $nodeId, int $ancestor = null)
	{
		$anc = !$ancestor ? $nodeId : $ancestor;
		$from = [ContentClosure::node($nodeId, true)];
		$to = ContentClosure::ancestors([$anc]);
		ContentClosure::_link($from, $to);
	}

	/**
	 * @throws Exception
	 */
	public static function __update__(int $nodeId, int $ancestor = null)
	{
		ContentClosure::node($nodeId);
		$parents = ContentClosure::parents([$nodeId]);
		if (!$ancestor) {
			ContentClosure::_move($nodeId, $ancestor);
		} else {
			if ($ancestor != $nodeId && (!in_array($ancestor, ArrayHelper::getColumn($parents, 'anc')) || empty($parents))) {
				ContentClosure::_move($nodeId, $ancestor);
			}
		}
	}

	/**
	 * @throws Exception
	 */
	public static function __delete__(array $nodes)
	{
		$descendants = ContentClosure::descendants($nodes);
		if (!empty($descendants)) {
			ContentClosure::_unlink($descendants, [], true);
		}
	}

	/**
	 * @throws Exception
	 */
	public static function _move(int $nodeId, $ancestor = null)
	{
		$descendants = ContentClosure::descendants([$nodeId]);
		if (!empty($descendants)) {
			$ancestors = ContentClosure::ancestors([$nodeId], false);
			$parent = ContentClosure::ancestors([$ancestor]);

			ContentClosure::_unlink($descendants, $ancestors);
			ContentClosure::_link($descendants, $parent);
		}
	}

	/**
	 * @param array $nodes
	 * @param array $toNodes
	 * @param bool $withNode
	 * @return void
	 */
	public static function _unlink(array $nodes, array $toNodes = [], bool $withNode = false)
	{
		$query = ContentClosure::find()->where(['IN', 'dsc', ArrayHelper::getColumn($nodes, 'dsc')]);
		if (!empty($toNodes)) {
			$query->andWhere(['IN', 'anc', ArrayHelper::getColumn($toNodes, 'anc')]);
		} else {
			$query->orWhere(['IN', 'anc', ArrayHelper::getColumn($nodes, 'dsc')]);
		}
		if (!$withNode) {
			$query->andFilterWhere(['>', 'lvl', 0]);
		}
		$data = $query->all();
		foreach ($data as $item) {
			try {
				$item->delete();
			} catch (StaleObjectException|Exception $e) {
				print_r($e);
			}
		}
	}

	/**
	 * @throws Exception
	 */
	public static function _link(array $fromNodes, array $toNodes)
	{
		$data = ContentClosure::map($fromNodes, $toNodes);
		if (!empty($data)) {
			foreach ($data as $item) {
				$node = ContentClosure::find()->where([
					'anc' => $item['anc'],
					'dsc' => $item['dsc'],
					'lvl' => $item['lvl'],
				])->one();
				if (empty($node)) {
					$node = new ContentClosure();
					$node->anc = $item['anc'];
					$node->dsc = $item['dsc'];
					$node->lvl = $item['lvl'];
					if (!$node->save()) {
						print_r($node->errors);
					}
				}
			}
		}
	}

	/**
	 * Извлекает родителей
	 *
	 * @param array $dsc
	 * @return array
	 */
	public static function parents(array $dsc): array
	{
		$query = ContentClosure::find()->select(['anc', 'dsc', 'lvl']);
		$query->andFilterWhere(['IN', 'dsc', $dsc]);
		$query->andFilterWhere(['lvl' => 1]);
		return $query->asArray()->all();
	}

	/**
	 * Извлекает детей
	 *
	 * @param array $anc
	 * @return array
	 */
	public static function children(array $anc): array
	{
		$query = ContentClosure::find()->select(['anc', 'dsc', 'lvl']);
		$query->andFilterWhere(['IN', 'anc', $anc]);
		$query->andFilterWhere(['lvl' => 1]);
		return $query->asArray()->all();
	}

	/**
	 * Извлекает предков
	 *
	 * @param array $dsc
	 * @param bool $withNode
	 * @return array
	 */
	public static function ancestors(array $dsc, bool $withNode = true): array
	{
		$query = ContentClosure::find()
			->select(['anc', 'dsc', 'lvl'])
			->andFilterWhere(['IN', 'dsc', $dsc]);
		if (!$withNode) {
			$query->andWhere(['>', 'lvl', 0]);
		}
		$query->orderBy([
			'lvl' => SORT_DESC,
			'dsc' => SORT_DESC,
			'anc' => SORT_ASC,
		]);
		return $query->asArray()->all();
	}

	/**
	 * Извлекает потомков
	 *
	 * @param array $dsc
	 * @param int $level
	 * @param bool $withNode
	 * @return array
	 */
	public static function descendants(array $dsc, int $level = 0, bool $withNode = true): array
	{
		$closure = ContentClosure::tableName();

		$query = ContentClosure::find()
			->select(['t.anc', 't.dsc', 't.lvl', 'p.anc AS parent_id'])
			->from("{$closure} AS t");

		$query->andWhere(['in', 't.anc', $dsc]);

		if (!$withNode) {
			$query->andWhere(['>', 't.lvl', 0]);
		}

		if ($level > 0) {
			$query->andWhere(['t.lvl' => $level]);
		}

		$query->leftJoin("{$closure} AS p", "p.dsc = t.dsc AND p.lvl = 1");

		return $query->asArray()->all();
	}

	/**
	 * Извлекает соседей
	 *
	 * @param array $nodes
	 * @param bool $withNode
	 * @return array
	 */
	public static function siblings(array $nodes, bool $withNode = true): array
	{
		$parents = ContentClosure::parents($nodes);
		$query = ContentClosure::find()->select(['anc', 'dsc', 'lvl']);
		$query->andWhere(['IN', 'anc', ArrayHelper::getColumn($parents, 'anc')]);
		$query->andWhere(['lvl' => 1]);

		if (!$withNode) {
			$query->andWhere(['NOT IN', 'dsc', ArrayHelper::getColumn($nodes, 'dsc')]);
		}

		return $query->asArray()->all();
	}

	/**
	 * @param array $nodes
	 * @param string $field
	 * @return array
	 */
	public static function nodes(array $nodes, string $field = 'dsc'): array
	{
		$in = ArrayHelper::getColumn($nodes, $field);

		$closure = ContentClosure::tableName();

		return ContentClosure::find()
			->select([
				't.anc',
				't.dsc',
				't.lvl',
				'p.anc AS parent_id',
				'h.dsc AS child_id',
			])
			->from("{$closure} AS t")
			->leftJoin(
				"{$closure} AS p",
				"p.dsc = t.dsc AND p.lvl = 1"
			)->leftJoin(
				"{$closure} AS h",
				"h.anc = t.dsc AND h.lvl = 1"
			)->andWhere(['IN', 't.dsc', $in])
			->asArray()->all();
	}

	/**
	 * @param array $nodes
	 * @return array
	 */
	public static function ancestorNodes(array $nodes): array
	{
		$ancestors = ContentClosure::ancestors($nodes);
		return ContentClosure::nodes($ancestors, 'anc');
	}

	/**
	 * @param array $nodes
	 * @return array
	 */
	public static function descendantNodes(array $nodes): array
	{
		$descendants = ContentClosure::descendants($nodes);
		return ContentClosure::nodes($descendants, 'dsc');
	}

	/**
	 * @param array $nodes
	 * @return array
	 */
	public static function siblingNodes(array $nodes): array
	{
		$siblings = ContentClosure::siblings($nodes);
		return ContentClosure::nodes($siblings, 'dsc');
	}

	/**
	 * @param array $nodes
	 * @return array
	 */
	public static function childNodes(array $nodes): array
	{
		$children = ContentClosure::children($nodes);
		return ContentClosure::nodes($children, 'dsc');
	}

	/**
	 * @param array $nodes
	 * @return array
	 */
	public static function parentNodes(array $nodes): array
	{
		$parents = ContentClosure::parents($nodes);
		return ContentClosure::nodes($parents, 'anc');
	}
}
