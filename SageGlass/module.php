<?php

declare(strict_types=1);
    class SageGlass extends IPSModule
    {
        public function Create()
        {
            //Never delete this line!
            parent::Create();

            //Properties
            $this->RegisterPropertyInteger('VariableTint', 0);
            $this->RegisterPropertyInteger('AutomodeState', 0);
            $this->RegisterPropertyInteger('LuxLevelSetPoint', 0);
            $this->RegisterPropertyInteger('Status', 0);
            $this->RegisterPropertyInteger('SensorVertical', 0);
            $this->RegisterPropertyInteger('SensorHorizontal', 0);

            //Profiles
            if (!IPS_VariableProfileExists('SBN.VariableTint')) {
                IPS_CreateVariableProfile('SBN.VariableTint', VARIABLETYPE_INTEGER);
                IPS_SetVariableProfileAssociation('SBN.VariableTint', 0, $this->Translate('Clear'), '', -1);
                IPS_SetVariableProfileAssociation('SBN.VariableTint', 28, '20 %', '', -1);
                IPS_SetVariableProfileAssociation('SBN.VariableTint', 49, '6 %', '', -1);
                IPS_SetVariableProfileAssociation('SBN.VariableTint', 82, '1 %', '', -1);
                IPS_SetVariableProfileAssociation('SBN.VariableTint', 127, $this->Translate('Power-up'), '', -1);
            }

            if (!IPS_VariableProfileExists('SBN.AutomodeStatus')) {
                IPS_CreateVariableProfile('SBN.AutomodeStatus', VARIABLETYPE_INTEGER);
                IPS_SetVariableProfileAssociation('SBN.AutomodeStatus', 0, $this->Translate('Disabled'), '', -1);
                IPS_SetVariableProfileAssociation('SBN.AutomodeStatus', 1, $this->Translate('Enabled'), '', -1);
                IPS_SetVariableProfileAssociation('SBN.AutomodeStatus', 2, $this->Translate('Off'), '', -1);
                IPS_SetVariableProfileAssociation('SBN.AutomodeStatus', 3, $this->Translate('In Override'), '', -1);
                IPS_SetVariableProfileAssociation('SBN.AutomodeStatus', 4, $this->Translate('Glare Control'), '', -1);
            }

            if (!IPS_VariableProfileExists('SBN.VariableTintPriorityStatus')) {
                IPS_CreateVariableProfile('SBN.VariableTintPriorityStatus', VARIABLETYPE_INTEGER);
                IPS_SetVariableProfileAssociation('SBN.VariableTintPriorityStatus', 0, $this->Translate('None'), '', -1);
                IPS_SetVariableProfileAssociation('SBN.VariableTintPriorityStatus', 1, $this->Translate('Low'), '', -1);
                IPS_SetVariableProfileAssociation('SBN.VariableTintPriorityStatus', 2, $this->Translate('Medium'), '', -1);
                IPS_SetVariableProfileAssociation('SBN.VariableTintPriorityStatus', 3, $this->Translate('High'), '', -1);
                IPS_SetVariableProfileAssociation('SBN.VariableTintPriorityStatus', 255, $this->Translate('Power-up'), '', -1);
            }

            if (!IPS_VariableProfileExists('SBN.LuxLevelSetPoint')) {
                IPS_CreateVariableProfile('SBN.LuxLevelSetPoint', VARIABLETYPE_INTEGER);
                IPS_SetVariableProfileAssociation('SBN.LuxLevelSetPoint', 0, '%d lx', '', -1);
                IPS_SetVariableProfileAssociation('SBN.LuxLevelSetPoint', 126973, $this->Translate('Power-up'), '', -1);
                IPS_SetVariableProfileValues('SBN.LuxLevelSetPoint', 0, 126972, 4);
            }

            if (!IPS_VariableProfileExists('SBN.Sensor')) {
                IPS_CreateVariableProfile('SBN.Sensor', VARIABLETYPE_INTEGER);
                IPS_SetVariableProfileAssociation('SBN.Sensor', 0, '%d lx', '', -1);
                IPS_SetVariableProfileAssociation('SBN.Sensor', 65535, $this->Translate('Power-up'), '', -1);
            }

            if (!IPS_VariableProfileExists('SBN.Status')) {
                IPS_CreateVariableProfile('SBN.Status', VARIABLETYPE_INTEGER);
                IPS_SetVariableProfileAssociation('SBN.Status', 0, $this->Translate('In transition'), '', -1);
                IPS_SetVariableProfileAssociation('SBN.Status', 1, $this->Translate('Holding'), '', -1);
                IPS_SetVariableProfileAssociation('SBN.Status', 255, $this->Translate('Power-up'), '', -1);
            }

            //Variables
            $this->RegisterVariableInteger('VariableTint', $this->Translate('Variable Tint'), 'SBN.VariableTint', 0);
            $this->EnableAction('VariableTint');
            $this->RegisterVariableInteger('AutomodeStatus', $this->Translate('Automode Status'), 'SBN.AutomodeStatus', 1);
            $this->EnableAction('AutomodeStatus');
            $this->RegisterVariableInteger('VariableTintPriorityStatus', $this->Translate('Variable Tint Priority Status'), 'SBN.VariableTintPriorityStatus', 2);
            $this->RegisterVariableInteger('LuxLevelSetPoint', $this->Translate('Lux Level Set Point'), 'SBN.LuxLevelSetPoint', 3);
            $this->EnableAction('LuxLevelSetPoint');
            $this->RegisterVariableInteger('Status', $this->Translate('Status'), 'SBN.Status', 4);
            $this->RegisterVariableInteger('SensorVertical', $this->Translate('Sensor (Vertical)'), 'SBN.Sensor', 5);
            $this->RegisterVariableInteger('SensorHorizontal', $this->Translate('Sensor (Horizontal)'), 'SBN.Sensor', 6);
        }

        public function Destroy()
        {
            //Never delete this line!
            parent::Destroy();
        }

        public function ApplyChanges()
        {
            //Never delete this line!
            parent::ApplyChanges();
            foreach ($this->GetMessageList() as $senderID => $messages) {
                foreach ($messages as $message) {
                    $this->UnregisterMessage($senderID, $message);
                }
            }
            $this->RegisterMessage($this->ReadPropertyInteger('VariableTint'), VM_UPDATE);
            $this->RegisterMessage($this->ReadPropertyInteger('AutomodeState'), VM_UPDATE);
            $this->RegisterMessage($this->ReadPropertyInteger('LuxLevelSetPoint'), VM_UPDATE);
            $this->RegisterMessage($this->ReadPropertyInteger('Status'), VM_UPDATE);
            $this->RegisterMessage($this->ReadPropertyInteger('SensorVertical'), VM_UPDATE);
            $this->RegisterMessage($this->ReadPropertyInteger('SensorHorizontal'), VM_UPDATE);
        }

        public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
        {
            if ($SenderID == $this->ReadPropertyInteger('VariableTint')) {
                $this->SetValue('VariableTint', $Data[0] & 0x7F);
            } elseif ($SenderID == $this->ReadPropertyInteger('AutomodeState')) {
                if ($Data[0] != 255) {
                    $this->SetValue('AutomodeStatus', $Data[0] & 0x07);
                    $this->SetValue('VariableTintPriorityStatus', ($Data[0] >> 4) & 0x07);
                }
            } elseif ($SenderID == $this->ReadPropertyInteger('LuxLevelSetPoint')) {
                if ($Data[0] != 65535) {
                    $this->SetValue('LuxLevelSetPoint', ($Data[0] & 0x7FFF)) * 4;
                } else {
                    $this->SetValue('LuxLevelSetPoint', 126972);
                }
            } elseif ($SenderID == $this->ReadPropertyInteger('Status')) {
                if ($Data[0] != 255) {
                    $this->SetValue('Status', $Data[0] & 0x01);
                } else {
                    $this->SetValue('Status', 255);
                }
            } elseif ($SenderID == $this->ReadPropertyInteger('SensorVertical')) {
                if ($Data[0] != 65535) {
                    $this->SetValue('SensorVertical', $Data[0] * 2);
                } else {
                    $this->SetValue('SensorVertical', 65535);
                }
            } elseif ($SenderID == $this->ReadPropertyInteger('SensorHorizontal')) {
                if ($Data[0] != 65535) {
                    $this->SetValue('SensorHorizontal', $Data[0] * 2);
                } else {
                    $this->SetValue('SensorHorizontal', 65535);
                }
            }
        }

        public function RequestAction($Ident, $Value)
        {
            switch ($Ident) {
                case 'VariableTint':
                    if ($Value == 127) {
                        return;
                    }
                    RequestAction($this->ReadPropertyInteger('VariableTint'), $Value);
                    break;

                case 'AutomodeStatus':
                    switch ($Value) {
                        case 1: //Enabled
                        case 2: //Off
                            RequestAction($this->ReadPropertyInteger('AutomodeState'), $Value);
                            break;

                        default:
                            echo $this->Translate('not supported');
                            return;
                    }
                    break;

                case 'LuxLevelSetPoint':
                    RequestAction($this->ReadPropertyInteger('LuxLevelSetPoint'), $Value / 4);
                    break;

                default:
                    return;

            }

            $this->SetValue($Ident, $Value);
        }
    }
