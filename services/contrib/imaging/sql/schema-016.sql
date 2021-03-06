--
-- (c) 2016 Siveo, http://siveo.net/
--
-- $Id$
--
-- This file is part of Pulse 2, http://siveo.net
--
-- Pulse 2 is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 2 of the License, or
-- (at your option) any later version.
--
-- Pulse 2 is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with Pulse 2; if not, write to the Free Software
-- Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
-- MA 02110-1301, USA.

SET SESSION character_set_server=UTF8;
SET NAMES 'utf8';

-- Update menu items for inventory
UPDATE `imaging`.`BootService`
SET
    `value` = 'COM32 hdt.c32
        APPEND dump_path=##PULSE2_INVENTORIES_DIR## timereboot=##PULSE2_PXE_TIME_REBOOT## tftp_ip=##PULSE2_PXE_TFTP_IP## hostname nomenu auto=\'dump;reboot\''
WHERE
    `id` = '2';

UPDATE version set Number = 16;
