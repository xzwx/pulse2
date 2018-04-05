# -*- coding: utf-8; -*-
#
# (c) 2016 siveo, http://www.siveo.net
#
# This file is part of Pulse 2, http://www.siveo.net
#
# Pulse 2 is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# Pulse 2 is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Pulse 2; if not, write to the Free Software
# Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
# MA 02110-1301, USA.

"""
kiosk database handler
"""
# SqlAlchemy
from sqlalchemy import create_engine, MetaData, select, func, and_, desc, or_, distinct
from sqlalchemy.orm import sessionmaker; Session = sessionmaker()
from sqlalchemy.exc import DBAPIError
from sqlalchemy import update
from datetime import date, datetime, timedelta
# PULSE2 modules
from mmc.database.database_helper import DatabaseHelper
from mmc.plugins.pkgs import get_xmpp_package, xmpp_packages_list
from pulse2.database.kiosk.schema import Profiles, Packages, Profile_has_package
# Imported last
import logging
import json
import time


class KioskDatabase(DatabaseHelper):
    """
    Singleton Class to query the xmppmaster database.

    """
    is_activated = False
    session = None

    def db_check(self):
        self.my_name = "kiosk"
        self.configfile = "kiosk.ini"
        return DatabaseHelper.db_check(self)

    def activate(self, config):

        if self.is_activated:
            return None
        self.config = config
        self.db = create_engine(self.makeConnectionPath(), pool_recycle = self.config.dbpoolrecycle, pool_size = self.config.dbpoolsize)
        print self.makeConnectionPath()
        if not self.db_check():
            return False
        self.metadata = MetaData(self.db)
        if not self.initMappersCatchException():
            self.session = None
            return False
        self.metadata.create_all()
        self.is_activated = True
        result = self.db.execute("SELECT * FROM kiosk.version limit 1;")
        re = [x.Number for x in result]
        #logging.getLogger().debug("xmppmaster database connected (version:%s)"%(re[0]))
        return True

    def initMappers(self):
        """
        Initialize all SQLalchemy mappers needed for the xmppmaster database
        """
        # No mapping is needed, all is done on schema file
        return

    def getDbConnection(self):
        NB_DB_CONN_TRY = 2
        ret = None
        for i in range(NB_DB_CONN_TRY):
            try:
                ret = self.db.connect()
            except DBAPIError, e:
                logging.getLogger().error(e)
            except Exception, e:
                logging.getLogger().error(e)
            if ret: break
        if not ret:
            raise "Database kiosk connection error"
        return ret

    # =====================================================================
    # kiosk FUNCTIONS
    # =====================================================================

    @DatabaseHelper._sessionm
    def get_profiles_list(self, session):
        """
        Return a list of all the existing profiles.
        The list contains all the elements of the profile.

            Returns:
                A list of all the founded entities.
        """
        ret = session.query(Profiles).all()
        lines = []
        for row in ret:
            lines.append(row.toDict())

        return lines

    @DatabaseHelper._sessionm
    def get_profiles_name_list(self, session):
        """
        Return a list of all the existing profiles.
        The list is a shortcut of the method get_profiles_list.

        Returns:
            A list of the names for all the founded entities.
        """
        ret = session.query(Profiles.name).all()
        lines = []
        for row in ret:
            lines.append(row[0])
        return lines


    @DatabaseHelper._sessionm
    def create_profile(self, session, name, active, packages):
        # refresh the packages in the database
        self.refresh_package_list()

        import time
        now = time.strftime('%Y-%m-%d %H:%M:%S')

        sql = """INSERT INTO `kiosk`.`profiles` VALUES('%s','%s', '%s', '%s');""" % ('0', name, active, now)

        session.execute(sql)
        session.commit()
        session.flush()

        # Search the id of the profile and save it into the variable id
        result = session.query(Profiles.id).filter(Profiles.name == name)
        result = result.first()
        id = 0
        for row in result:
            id = str(row)

        session.query(Profile_has_package).filter(Profile_has_package.profil_id == id).delete()

        # The profile is now created, but the packages are not linked to it nor added into database.
        # If the package list is not empty, then firstly we get the status and the uuid for each packages
        if len(packages) > 0 :
            for status in packages.keys():
                for uuid in packages[status]:

                    # get the package id and link it with the profile
                    result = session.query(Packages.id).filter(Packages.package_uuid == uuid)
                    result = result.first()
                    id_package = 0
                    for row in result:
                        id_package = str(row)

                    profile = Profile_has_package()
                    profile.profil_id = id
                    profile.package_id = id_package
                    profile.package_status = status

                    session.add(profile)
                    session.commit()
                    session.flush()
        return id

    @DatabaseHelper._sessionm
    def refresh_package_list(self, session):
        package_list = xmpp_packages_list()

        for ref_pkg in package_list:
            result = session.query(Packages.id).filter(Packages.package_uuid == ref_pkg['uuid']).all()

            # Create a Package object to interact with the database
            package = get_xmpp_package(ref_pkg['uuid'])
            os = json.loads(package).keys()[1]

            # Prepare a package object for the transaction with the database
            pkg = Packages()
            pkg.name = ref_pkg['name']
            pkg.version_package = ref_pkg['version']
            pkg.software = ref_pkg['software']
            pkg.description = ref_pkg['description']
            pkg.version_software = 0
            pkg.package_uuid = ref_pkg['uuid']
            pkg.os = os

            # If the package is not registered into database, it is added. Else it is updated
            if len(result) == 0:
                session.add(pkg)
                session.commit()
                session.flush()
            else:
                sql = """UPDATE `package` set name='%s', version_package='%s', software='%s',\
                description='%s', package_uuid='%s', os='%s' WHERE package_uuid='%s';""" % (
                    ref_pkg['name'], ref_pkg['version'], ref_pkg['software'], ref_pkg['description'], ref_pkg['uuid'], os, ref_pkg['uuid'])

                session.execute(sql)
                session.commit()
                session.flush()

        # Now we need to verify if all the registered packages are still existing into the server
        # TODO

    @DatabaseHelper._sessionm
    def delete_profile(self, session, name):
        """
        Delete the named profile from the table profiles.
        This method delete the profiles which have the specified name.

        Args:
            name: the name of the profile

        Returns:
            Boolean: True if success, else False
        """
        try:
            result = session.query(Profiles.id).filter(Profiles.name == name)
            result = result.first()
            id = 0
            for row in result:
                id = str(row)

            ret = session.query(Profile_has_package).filter(Profile_has_package.profil_id == id).delete()

            ret2 = session.query(Profiles).filter(Profiles.name == name).delete()
            session.commit()
            session.flush()
            return True

        except Exception, e:
            return False

    @DatabaseHelper._sessionm
    def get_profile(self, session, name):
        """
        Return the information of the specified profile. It include also it's packages
            Params:
                name = (str) the name of the profile
            Returns:
                A list of all the founded entities.
        """
        ret = session.query(Profiles).all()
        lines = []
        for row in ret:
            lines.append(row.toDict())
        return lines
