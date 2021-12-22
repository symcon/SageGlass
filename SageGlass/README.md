# SageGlass
Dieses Modul erlaubt die Einbindung SageGlass SIM II Controller über BACnet/IP. 

### Inhaltsverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)

### 1. Funktionsumfang

* Integriert die Ansteuerung von SageGlass Zonen und zeigt den aktuellen Zustand inkl. der übermittelten Lux-Werte an.

### 2. Voraussetzungen

- IP-Symcon Enterprise ab Version 6.0 mit BACnet Erweiterung

### 3. Software-Installation

* Über den Module Store das 'SageGlass'-Modul installieren.

### 4. Einrichten in IP-Symcon

* Es wird empfohlen alle BACnet Objekte auf dem SageGlass SIM II Controller automatisch erstellen zu lassen, wodurch die einzelnen Datenpunkte in IP-Symcon verfügbar gemacht werden. Im zweiten Schritt kann nachfolgendes Skript verwendet werden, um die SageGlass Instanzen für die Zonen zu erstellen, welche die Informationen schöner aufbereiten. Es ist auch möglich die Verknüpfungen manuell durchzuführen, indem alle Eigenschaften auf die korrekten BACnet Objekte verknüpft werden. Die Namen der Eigenschaften entsprechen dabei den Beschreibungen der BACnet Objekte und sollten auf die "Preset Value" Variable verknüpft werden.  

```
<?php

// Not every zone has a sensor - Normally each facade will have a vertical/horizontal sensor
// Fill these arrays with the sensor -> zone information
// e.g. Sensor 1 (Vertical) is used for Zone 10, Zone 20 and Zone 25
$sensorVertical = [
    1 => [10, 20, 25]
];
$sensorHorizontal = [];

$zones = 250;
for ($i = 1; $i <= $zones; $i++) {
	$variableTintID = searchBACnet(2 /* Analoge Value */, $i + 1 /* Zone 1 = 2 */);
	$automodeStateID = searchBACnet(2 /* Analoge Value */, $i + 2001 /* Zone 1 = 2002 */);
	$luxLevelSetPointID = searchBACnet(2 /* Analoge Value */, $i + 4001 /* Zone 1 = 4002 */);
	$statusID = searchBACnet(0 /* Analoge Input */, $i + 4000 /* Zone 1 = 4001 */);

    // Search for the our sensor the vertical sensor matrix
    $verticalSensorID = searchSensorID($i, $sensorVertical);
    if ($verticalSensorID) {
	    $verticalSensorID = searchBACnet(0 /* Analoge Input */, $verticalSensorID + 2000 /* Sensor 1 = 2001 */);
    }

    // Search for the our sensor the vertical sensor matrix
    $horizontalSensorID = searchSensorID($i, $sensorHorizontal);
    if ($horizontalSensorID) {
	    $horizontalSensorID = searchBACnet(0 /* Analoge Input */, $horizontalSensorID + 2000 /* Sensor 1 = 2001 */);
    }

	if (!@IPS_GetObjectIDByIdent("SageGlassZone" . $i)) {
		if ($variableTintID && $automodeStateID && $luxLevelSetPointID && $verticalSensorID && $horizontalSensorID && $statusID) {
			echo "Creating Zone " . $i . "..." . PHP_EOL;
			$id = IPS_CreateInstance("{67CEA419-A625-703E-2BE6-BF51B3C913B9}");
			IPS_SetName($id, "Zone " . $i);
			IPS_SetIdent($id, "SageGlassZone" . $i);
			IPS_SetProperty($id, "VariableTint", $variableTintID);
			IPS_SetProperty($id, "AutomodeState", $automodeStateID);
			IPS_SetProperty($id, "LuxLevelSetPoint", $luxLevelSetPointID);
			IPS_SetProperty($id, "Status", $statusID);
			IPS_SetProperty($id, "SensorVertical", $verticalSensorID);
			IPS_SetProperty($id, "SensorHorizontal", $horizontalSensorID);
			IPS_ApplyChanges($id);
		}
	}
}

function searchSensorID($zone, $sensorMatrix) {
    foreach($sensorMatrix as $sensor => $matrix) {
        foreach($matrix as $sensorZone) {
            if ($zone == $sensorZone) {
                return $sensor;
            }
        }
    }
    return 0;
}

function searchBACnet($objectType, $instanceNumber) {
	$ids = IPS_GetInstanceListByModuleID("{CD5D5D10-3743-DA88-F16C-8B65CF4103F9}");
	foreach ($ids as $id) {
		if ((IPS_GetProperty($id, "ObjectType") == $objectType) && (IPS_GetProperty($id, "InstanceNumber") == $instanceNumber)) {
			return IPS_GetObjectIDByIdent("PresentValue", $id);
		}
	}
	return 0;
}
```
