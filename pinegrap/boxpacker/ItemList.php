<?php
/**
 * Box packing (3D bin packing, knapsack problem)
 * @package BoxPacker
 * @author Doug Wright
 */
namespace DVDoug\BoxPacker;

/**
 * List of items to be packed, ordered by volume
 * @author Doug Wright
 * @package BoxPacker
 */
class ItemList extends \SplMaxHeap
{

    /**
     * Compare elements in order to place them correctly in the heap while sifting up.
     * @see \SplMaxHeap::compare()
     */
    public function compare($itemA, $itemB)
    {
        if ($itemA->getVolume() > $itemB->getVolume()) {
            return 1;
        } elseif ($itemA->getVolume() < $itemB->getVolume()) {
            return -1;
        } else {
            return $itemA->getWeight() - $itemB->getWeight();
        }
    }

    /**
     * Get copy of this list as a standard PHP array
     * @return array
     */
    public function asArray()
    {
        //$return = [];
        $return = array();
        foreach (clone $this as $item) {
            $return[] = $item;
        }
        return $return;
    }
}
