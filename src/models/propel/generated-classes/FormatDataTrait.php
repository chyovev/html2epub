<?php

use Propel\Runtime\Map\TableMap;

trait FormatDataTrait {

    ///////////////////////////////////////////////////////////////////////////
    // call parent function but keytype is fieldname by default
    public function toArray(
        $keyType                = TableMap::TYPE_FIELDNAME,
        $includeLazyLoadColumns = true,
        $alreadyDumpedObjects   = [],
        $includeForeignObjects  = false
    ) {
        return parent::toArray($keyType, $includeLazyLoadColumns, $alreadyDumpedObjects, $includeForeignObjects);
    }

    ///////////////////////////////////////////////////////////////////////////
    public function fromArray($arr, $keyType = TableMap::TYPE_FIELDNAME) {
        parent::fromArray($arr, $keyType);
    }

}