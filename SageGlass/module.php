<?php

declare(strict_types=1);
	class SageGlass extends IPSModule
	{
		const IDENTS = [
			'VariableTint',
			'AutomodeStatus',
			'VariableTintPriorityStatus',
			'LuxLevelSetPoint',
			'Sensor',
			'Status'
		];

		public function Create()
		{
			//Never delete this line!
			parent::Create();

            $this->RegisterPropertyInteger('VariableTint', 0);
            $this->RegisterPropertyInteger('AutomodeState', 0);
            $this->RegisterPropertyInteger('VariableTintPriorityStatus', 0);
            $this->RegisterPropertyInteger('LuxLevelSetPoint', 0);
            $this->RegisterPropertyInteger('Sensor', 0);
            $this->RegisterPropertyInteger('Status', 0);

			if (!IPS_VariableProfileExists('SBN.VariableTint')) {
                IPS_CreateVariableProfile('SBN.VariableTint', VARIABLETYPE_INTEGER);
                IPS_SetVariableProfileAssociation('SBN.VariableTint', 0, 'Clear', '', -1);
                IPS_SetVariableProfileAssociation('SBN.VariableTint', 127, 'Power-up', '', -1);
                IPS_SetVariableProfileAssociation('SBN.VariableTint', 28, '20 %', '', -1);
                IPS_SetVariableProfileAssociation('SBN.VariableTint', 49, '6 %', '', -1);
                IPS_SetVariableProfileAssociation('SBN.VariableTint', 82, '1 %', '', -1);
                IPS_SetVariableProfileValues('SBN.VariableTint', 0, 127, 1);
			}

			if (!IPS_VariableProfileExists('SBN.AutomodeStatus')) {
				IPS_CreateVariableProfile('SBN.AutomodeStatus', VARIABLETYPE_INTEGER);
                IPS_SetVariableProfileAssociation('SBN.AutomodeStatus', 0, 'Disabled', '', -1);
                IPS_SetVariableProfileAssociation('SBN.AutomodeStatus', 1, 'Enabled', '', -1);
                IPS_SetVariableProfileAssociation('SBN.AutomodeStatus', 2, 'Off', '', -1);
                IPS_SetVariableProfileAssociation('SBN.AutomodeStatus', 3, 'In Override', '', -1);
                IPS_SetVariableProfileAssociation('SBN.AutomodeStatus', 4, 'Glare Control', '', -1);
                IPS_SetVariableProfileAssociation('SBN.AutomodeStatus', 5, 'Not Applicable', '', -1);
                IPS_SetVariableProfileAssociation('SBN.AutomodeStatus', 6, 'Not Applicable', '', -1);
                IPS_SetVariableProfileAssociation('SBN.AutomodeStatus', 7, 'Not Applicable', '', -1);
			}
			if (!IPS_VariableProfileExists('SBN.VariableTintPriorityStatus')) {
				IPS_CreateVariableProfile('SBN.VariableTintPriorityStatus', VARIABLETYPE_INTEGER);
				IPS_SetVariableProfileAssociation('SBN.VariableTintPriorityStatus', 0, 'None', '', -1);
				IPS_SetVariableProfileAssociation('SBN.VariableTintPriorityStatus', 1, 'Low', '', -1);
				IPS_SetVariableProfileAssociation('SBN.VariableTintPriorityStatus', 2, 'Medium', '', -1);
				IPS_SetVariableProfileAssociation('SBN.VariableTintPriorityStatus', 3, 'High', '', -1);
				IPS_SetVariableProfileAssociation('SBN.VariableTintPriorityStatus', 255, 'Power-up', '', -1);
			}

			if (!IPS_VariableProfileExists('SBN.LuxLevelSetPoint')) {
                IPS_CreateVariableProfile('SBN.LuxLevelSetPoint', VARIABLETYPE_INTEGER);
                IPS_SetVariableProfileText('SBN.LuxLevelSetPoint', '', 'lx');
				IPS_SetVariableProfileAssociation('SBN.LuxLevelSetPoint', 65535, 'Power-up', '', -1);
			}

			if (!IPS_VariableProfileExists('SBN.Sensor')) {
				IPS_CreateVariableProfile('SBN.Sensor', VARIABLETYPE_INTEGER);
                IPS_SetVariableProfileText('SBN.Sensor', '', 'lx');
				IPS_SetVariableProfileAssociation('SBN.Sensor', 65535, 'Power-up', '', -1);

			}
				
			if (!IPS_VariableProfileExists('SBN.Status')) {
                IPS_CreateVariableProfile('SBN.Status', VARIABLETYPE_INTEGER);
                IPS_SetVariableProfileAssociation('SBN.Status', 0, 'In transition', '', -1);
                IPS_SetVariableProfileAssociation('SBN.Status', 1, 'Holding', '', -1);
                IPS_SetVariableProfileAssociation('SBN.Status', 255, 'Power-up', '', -1);
			}
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
            $messages = $this->GetMessageList();
			// foreach($messages as $instanceID => $message) {
            //     $this->UnregisterMessage($instanceID, $message);
			// }
			foreach(self::IDENTS as $ident) {
                $this->setupVariable($ident);
			}

		}

		private function setupVariable($ident) {
			$this->RegisterVariableInteger($ident, $ident, 'SBN.' . $ident, 0);
			if ($ident == 'AutomodeStatus' || $ident == 'VariableTintPriorityStatus') {
                $ident = 'AutomodeState';
			}
			$source = $this->ReadPropertyInteger($ident);
			if ($source) {
				$this->RegisterMessage($source, VM_UPDATE);
			}	
			
		}

		public function MessageSink($TimeStamp, $SenderID, $Message, $Data) {
			if ($SenderID == $this->ReadPropertyInteger('VariableTint')) {
				$this->SetValue('VariableTint', $Data[0] & 0x7F);
			} elseif ($SenderID == $this->ReadPropertyInteger('AutomodeState')) {
				$this->SetValue('AutomodeStatus', $Data[0] & 0x07);
				$this->SetValue('VariableTintPriorityStatus', ($Data[0] >> 4) & 0x07);
			} elseif ($SenderID == $this->ReadPropertyInteger('LuxLevelSetPoint')) {
				$this->SetValue('LuxLevelSetPoint', ($Data[0] & 0x7FFF)) / 4;
			} elseif ($SenderID == $this->ReadPropertyInteger('Sensor')) {
				if ($Data[0] != 65535) {
					$this->SetValue('Sensor', $Data[0] / 2);
				} else {
					$this->SetValue('Sensor', 65535);
				}
			} elseif ($SenderID == $this->ReadPropertyInteger('Status')) {
                $this->SetValue('Status', $Data[0] & 0x01);
			}
			//TODO Default power state
		}
	}