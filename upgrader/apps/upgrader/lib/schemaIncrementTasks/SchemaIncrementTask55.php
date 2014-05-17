<?php

/**
 * OrangeHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 OrangeHRM Inc., http://www.orangehrm.com
 *
 * OrangeHRM is free software; you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 *
 * OrangeHRM is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with this program;
 * if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor,
 * Boston, MA  02110-1301, USA
 *
 */

/**
 * Upgrade for menu changes, new ui changes and leave changes:
 * 
 * TODO: Improve
 * **************** IMPORTANT *********************
 * UPGRADE IS NOT ONLY COMPATIBLE WITH A PLAIN OS INSTALL.
 * If ab addon is installed, those add-ons may
 * have added records to ohrm_user_role and related tables. Therefore, the SQL in 
 * here has to be improved to not hard code IDs.
 * 
 * For an example, see: rangehrmRegionalAdminAuthorizationPlugin/trunk/install/dbscript.sql
 * ******************************************************
 */
class SchemaIncrementTask55 extends SchemaIncrementTask {
    public $userInputs;

    public function execute() {
        $this->incrementNumber = 55;
        parent::execute();

        $result = array();

        foreach ($this->sql as $sql) {
            $result[] = $this->upgradeUtility->executeSql($sql);
        }

        $this->checkTransactionComplete($result);
        $this->updateOhrmUpgradeInfo($this->transactionComplete, $this->incrementNumber);
        $this->upgradeUtility->finalizeTransaction($this->transactionComplete);
        $this->upgradeUtility->closeDbConnection();
    }

    public function getUserInputWidgets() {
        
    }

    public function setUserInputs() {
        
    }

    public function loadSql() {

        $sql[] = "create table `ohrm_menu_item` (
                    `id` int not null auto_increment, 
                    `menu_title` varchar(255) not null, 
                    `screen_id` int default null,
                    `parent_id` int default null,
                    `level` tinyint not null,
                    `order_hint` int not null,
                    `url_extras` varchar(255) default null, 
                    `status` tinyint not null default 1,
                    primary key (`id`)
                 ) engine=innodb default charset=utf8;";
        
        $sql[] = "CREATE TABLE ohrm_leave_type (
                `id` int unsigned not null auto_increment,
                `name` varchar(50) not null,
                `deleted` tinyint(1) not null default 0,
                `operational_country_id` int unsigned default null,
                primary key  (`id`)
              ) engine=innodb default charset=utf8;";

        $sql[] = "CREATE TABLE ohrm_leave_entitlement (
                    `id` int unsigned not null auto_increment,
                    emp_number int(7) not null,
                    no_of_days int not null,
                    days_used decimal(4,2) not null default 0,
                    leave_type_id int unsigned not null,
                    from_date datetime not null,
                    to_date datetime,
                    credited_date datetime,
                    note varchar(255) default null, 
                    entitlement_type decimal(6,2) not null,
                    `deleted` tinyint(1) not null default 0,
                    created_by_id int(10),
                    created_by_name varchar(255),
                    PRIMARY KEY(`id`)
                  ) ENGINE = INNODB DEFAULT CHARSET=utf8;";

        $sql[] = "CREATE TABLE `ohrm_leave_request` (
                    `id` int unsigned NOT NULL auto_increment,
                    `leave_type_id` int unsigned NOT NULL,
                    `date_applied` date NOT NULL,
                    `emp_number` int(7) NOT NULL,
                    `comments` varchar(256) default NULL,
                    PRIMARY KEY  (`id`)
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

        $sql[] = "CREATE TABLE `ohrm_leave` (
                    `id` int(11) NOT NULL  auto_increment,
                    `date` date default NULL,
                    `length_hours` decimal(6,2) unsigned default NULL,
                    `length_days` decimal(4,2) unsigned default NULL,
                    `status` smallint(6) default NULL,
                    `comments` varchar(256) default NULL,
                    `leave_request_id`int unsigned NOT NULL,
                    `leave_type_id` int unsigned NOT NULL,
                    `emp_number` int(7) NOT NULL,
                    `start_time` time default NULL,
                    `end_time` time default NULL,
                    PRIMARY KEY  (`id`),
                    KEY `leave_request_type_emp`(`leave_request_id`,`leave_type_id`,`emp_number`),
                    KEY `request_status` (`leave_request_id`,`status`)
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    
        $sql[] = "create TABLE `ohrm_leave_leave_entitlement` (
                `id` int(11) NOT NULL   auto_increment,
                `leave_id` int(11) NOT NULL,
                `entitlement_id` int unsigned NOT NULL,
                `length_days` decimal(4,2) unsigned default NULL,
                PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        
         $sql[] = "CREATE TABLE `ohrm_leave_period` (
                    `leave_period_id` int(11) NOT NULL,
                    `leave_period_start_date` date NOT NULL,
                    `leave_period_end_date` date NOT NULL,
                    PRIMARY KEY (`leave_period_id`)
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        
        $sql[] = "alter table ohrm_leave_type
                add foreign key (operational_country_id)
                    references ohrm_operational_country(id) on delete set null;";
        
        $sql[] = "alter table ohrm_leave_entitlement
                add foreign key (leave_type_id)
                    references ohrm_leave_type(id) on delete cascade;";

        $sql[] = "alter table ohrm_leave_entitlement
                    add foreign key (emp_number)
                        references hs_hr_employee(emp_number) on delete cascade;";

        $sql[] = "alter table ohrm_leave_entitlement
            add foreign key (created_by_id)
                references ohrm_user(`id`) on delete set null;";

        $sql[] = "alter table ohrm_leave_request
            add constraint foreign key (emp_number)
                references hs_hr_employee (emp_number) on delete cascade;";

        $sql[] = "alter table ohrm_leave_request
            add constraint foreign key (leave_type_id)
                references ohrm_leave_type (id) on delete cascade;";

        $sql[] = "alter table ohrm_leave
            add foreign key (leave_request_id)
                references ohrm_leave_request(id) on delete cascade;";

        $sql[] = "alter table ohrm_leave
            add constraint foreign key (leave_type_id)
                references ohrm_leave_type (id) on delete cascade";

        $sql[] = "alter table ohrm_leave
            add constraint foreign key (leave_type_id)
                references ohrm_leave_type (id) on delete cascade";

        $sql[] = "alter table ohrm_leave_leave_entitlement
            add constraint foreign key (entitlement_id)
                references ohrm_leave_entitlement (id) on delete cascade";

        $sql[] = "alter table ohrm_leave_leave_entitlement
            add constraint foreign key (leave_id)
                references ohrm_leave (id) on delete cascade";


        $sql[] = "alter table ohrm_menu_item 
               add constraint foreign key (screen_id)
                                     references ohrm_screen(id) on delete cascade;";        

        $sql[] = "INSERT INTO `hs_hr_config`(`key`, `value`) VALUES ('themeName', 'default'),
                    ('leave.entitlement_consumption_algorithm', 'FIFOEntitlementConsumptionStrategy'),
                    ('leave.work_schedule_implementation', 'BasicWorkSchedule');";
        
        $sql[] = "DELETE FROM `ohrm_user_role` WHERE id = 6;";
        
        $sql[] = "INSERT INTO `ohrm_user_role` (`id`, `name`, `display_name`, `is_assignable`, `is_predefined`) VALUES
                (6, 'HiringManager', 'HiringManager', 0, 1),
                (7, 'Reviewer', 'Reviewer', 0, 1);";


        $sql[] = "INSERT INTO ohrm_screen (`id`, `name`, `module_id`, `action_url`) VALUES
                    (20, 'General Information', 2, 'viewOrganizationGeneralInformation'),
                    (21, 'Location List', 2, 'viewLocations'),
                    (22, 'View Company Structure', 2, 'viewCompanyStructure'),
                    (23, 'Job Title List', 2, 'viewJobTitleList'),
                    (24, 'Pay Grade List', 2, 'viewPayGrades'),
                    (25, 'Employment Status List', 2, 'employmentStatus'),
                    (26, 'Job Category List', 2, 'jobCategory'),
                    (27, 'Work Shift List', 2, 'workShift'),
                    (28, 'Skill List', 2, 'viewSkills'),
                    (29, 'Education List', 2, 'viewEducation'),
                    (30, 'License List', 2, 'viewLicenses'),
                    (31, 'Language List', 2, 'viewLanguages'),
                    (32, 'Membership List', 2, 'membership'),
                    (33, 'Nationality List', 2, 'nationality'),
                    (34, 'Add/Edit Mail Configuration', 2, 'listMailConfiguration'),
                    (35, 'Notification List', 2, 'viewEmailNotification'),
                    (36, 'Customer List', 2, 'viewCustomers'),
                    (37, 'Project List', 2, 'viewProjects'),
                    (38, 'Localization', 2, 'localization'),
                    (39, 'Module Configuration', 2, 'viewModules'),
                    (40, 'Configure PIM', 3, 'configurePim'),
                    (41, 'Custom Field List', 3, 'listCustomFields'),
                    (42, 'Data Import', 2, 'pimCsvImport'),
                    (43, 'Reporting Method List', 3, 'viewReportingMethods'),
                    (44, 'Termination Reason List', 3, 'viewTerminationReasons'),
                    (45, 'PIM Reports List', 1, 'viewDefinedPredefinedReports'),
                    (46, 'View MyInfo', 3, 'viewMyDetails'),
                    (47, 'Define Leave Period', 4, 'defineLeavePeriod'),
                    (48, 'View My Leave List', 4, 'viewMyLeaveList'),
                    (49, 'Apply Leave', 4, 'applyLeave'),
                    (50, 'Define Timesheet Start Date', 5, 'defineTimesheetPeriod'),
                    (51, 'View My Timesheet', 5, 'viewMyTimesheet'),
                    (52, 'View Employee Timesheet', 5, 'viewEmployeeTimesheet'),
                    (53, 'View My Attendance', 6, 'viewMyAttendanceRecord'),
                    (54, 'Punch In/Out', 6, 'punchIn'),
                    (55, 'View Employee Attendance', 6, 'viewAttendanceRecord'),
                    (56, 'Attendance Configuration', 6, 'configure'),
                    (57, 'View Employee Report Criteria', 5, 'displayProjectReportCriteria'),
                    (58, 'View Project Report Criteria', 5, 'displayEmployeeReportCriteria'),
                    (59, 'View Attendance Report Criteria', 5, 'displayAttendanceSummaryReportCriteria'),
                    (60, 'Candidate List', 7, 'viewCandidates'),
                    (61, 'Vacancy List', 7, 'viewJobVacancy'),
                    (62, 'KPI List', 9, 'listDefineKpi'),
                    (63, 'Add/Edit KPI', 9, 'saveKpi'),
                    (64, 'Copy KPI', 9, 'copyKpi'),
                    (65, 'Add Review', 9, 'saveReview'),
                    (66, 'Review List', 9, 'viewReview'),
                    (67, 'View Time Module', 5, 'viewTimeModule'),
                    (68, 'View Leave Module', 4, 'viewLeaveModule'),
                    (69, 'Leave Entitlements', 4, 'viewLeaveEntitlements'),
                    (70, 'My Leave Entitlements', 4, 'viewMyLeaveEntitlements'),
                    (71, 'Delete Leave Entitlements', 4, 'deleteLeaveEntitlements'),
                    (72, 'Add Leave Entitlement', 4, 'addLeaveEntitlement'),
                    (73, 'Edit Leave Entitlement', 4, 'editLeaveEntitlement'),
                    (74, 'View Admin Module', 2, 'viewAdminModule'),
                    (75, 'View PIM Module', 3, 'viewPimModule'),
                    (76, 'View Recruitment Module', 7, 'viewRecruitmentModule'),
                    (77, 'View Performance Module', 9, 'viewPerformanceModule'),
                    (78, 'Leave Balance Report', 4, 'viewLeaveBalanceReport'),
                    (79, 'My Leave Balance Report', 4, 'viewMyLeaveBalanceReport');";

        $sql[] = "INSERT INTO ohrm_menu_item (`id`, `menu_title`, `screen_id`, `parent_id`, `level`, `order_hint`, `url_extras`, `status`) VALUES
                    (1, 'Admin', 74, NULL, 1, 100, NULL, 1),
                    (2, 'Users', 1, 1, 2, 100, NULL, 1),
                    (3, 'Project Info', NULL, 1, 2, 200, NULL, 1),
                    (4, 'Customers', 36, 3, 3, 100, NULL, 1),
                    (5, 'Projects', 37, 3, 3, 200, NULL, 1),
                    (6, 'Job', NULL, 1, 2, 300, NULL, 1),
                    (7, 'Job Titles', 23, 6, 3, 100, NULL, 1),
                    (8, 'Pay Grades', 24, 6, 3, 200, NULL, 1),
                    (9, 'Employment Status', 25, 6, 3, 300, NULL, 1),
                    (10, 'Job Categories', 26, 6, 3, 400, NULL, 1),
                    (11, 'Work Shifts', 27, 6, 3, 500, NULL, 1),
                    (12, 'Organization', NULL, 1, 2, 400, NULL, 1),
                    (13, 'General Information', 20, 12, 3, 100, NULL, 1),
                    (14, 'Locations', 21, 12, 3, 200, NULL, 1),
                    (15, 'Structure', 22, 12, 3, 300, NULL, 1),
                    (16, 'Qualifications', NULL, 1, 2, 500, NULL, 1),
                    (17, 'Skills', 28, 16, 3, 100, NULL, 1),
                    (18, 'Education', 29, 16, 3, 200, NULL, 1),
                    (19, 'Licenses', 30, 16, 3, 300, NULL, 1),
                    (20, 'Languages', 31, 16, 3, 400, NULL, 1),
                    (21, 'Memberships', 32, 1, 2, 600, NULL, 1),
                    (22, 'Nationalities', 33, 1, 2, 700, NULL, 1),
                    (23, 'Email Notifications', NULL, 1, 2, 800, NULL, 1),
                    (24, 'Configuration', 34, 23, 3, 100, NULL, 1),
                    (25, 'Subscribe', 35, 23, 3, 200, NULL, 1),
                    (26, 'Configuration', NULL, 1, 2, 900, NULL, 1),
                    (27, 'Localization', 38, 26, 3, 100, NULL, 1),
                    (28, 'Modules', 39, 26, 3, 200, NULL, 1),
                    (30, 'PIM', 75, NULL, 1, 200, NULL, 1),
                    (31, 'Configuration', NULL, 30, 2, 100, NULL, 1),
                    (32, 'Optional Fields', 40, 31, 3, 100, NULL, 1),
                    (33, 'Custom Fields', 41, 31, 3, 200, NULL, 1),
                    (34, 'Data Import', 42, 31, 3, 300, NULL, 1),
                    (35, 'Reporting Methods', 43, 31, 3, 400, NULL, 1),
                    (36, 'Termination Reasons', 44, 31, 3, 500, NULL, 1),
                    (37, 'Employee List', 5, 30, 2, 200, '/reset/1', 1),
                    (38, 'Add Employee', 4, 30, 2, 300, NULL, 1),
                    (39, 'Reports', 45, 30, 2, 400, '/reportGroup/3/reportType/PIM_DEFINED', 1),
                    (40, 'My Info', 46, NULL, 1, 700, NULL, 1),
                    (41, 'Leave', 68, NULL, 1, 300, NULL, 1),
                    (42, 'Configure', NULL, 41, 2, 400, NULL, 0),
                    (43, 'Leave Period', 47, 42, 3, 100, NULL, 0),
                    (44, 'Leave Types', 7, 42, 3, 200, NULL, 0),
                    (45, 'Work Week', 14, 42, 3, 300, NULL, 0),
                    (46, 'Holidays', 11, 42, 3, 400, NULL, 0),
                    (47, 'Leave Summary', 18, 41, 2, 500, NULL, 0),
                    (48, 'Leave List', 16, 41, 2, 600, '/reset/1', 0),
                    (49, 'Assign Leave', 17, 41, 2, 700, NULL, 0),
                    (50, 'My Leave', 48, 41, 2, 800, '/reset/1', 0),
                    (51, 'Apply', 49, 41, 2, 900, NULL, 0),
                    (52, 'Time', 67, NULL, 1, 400, NULL, 1),
                    (53, 'Timesheets', NULL, 52, 2, 100, NULL, 1),
                    (54, 'My Timesheets', 51, 53, 3, 100, NULL, 0),
                    (55, 'Employee Timesheets', 52, 53, 3, 200, NULL, 0),
                    (56, 'Attendance', NULL, 52, 2, 200, NULL, 1),
                    (57, 'My Records', 53, 56, 3, 100, NULL, 0),
                    (58, 'Punch In/Out', 54, 56, 3, 200, NULL, 0),
                    (59, 'Employee Records', 55, 56, 3, 300, NULL, 0),
                    (60, 'Configuration', 56, 56, 3, 400, NULL, 0),
                    (61, 'Reports', NULL, 52, 2, 300, NULL, 1),
                    (62, 'Project Reports', 57, 61, 3, 100, '?reportId=1', 0),
                    (63, 'Employee Reports', 58, 61, 3, 200, '?reportId=2', 0),
                    (64, 'Attendance Summary', 59, 61, 3, 300, '?reportId=4', 0),
                    (65, 'Recruitment', 76, NULL, 1, 500, NULL, 1),
                    (66, 'Candidates', 60, 65, 2, 100, NULL, 1),
                    (67, 'Vacancies', 61, 65, 2, 200, NULL, 1),
                    (68, 'Performance', 77, NULL, 1, 600, NULL, 1),
                    (69, 'KPI List', 62, 68, 2, 100, NULL, 1),
                    (70, 'Add KPI', 63, 68, 2, 200, NULL, 1),
                    (71, 'Copy KPI', 64, 68, 2, 300, NULL, 1),
                    (72, 'Add Review', 65, 68, 2, 400, NULL, 1),
                    (73, 'Reviews', 66, 68, 2, 500, '/mode/new', 1),
                    (74, 'Entitlements', NULL, 41, 2, 100, NULL, 0),
                    (75, 'Add Entitlements', 72, 74, 3, 100, NULL, 0),
                    (76, 'My Entitlements', 70, 74, 3, 200, '/reset/1', 0),
                    (77, 'Employee Entitlements', 69, 74, 3, 300, '/reset/1', 0),
                    (78, 'Reports', NULL, 41, 2, 200, NULL, 0),
                    (79, 'Leave Balance', 78, 78, 3, 100, NULL, 0),
                    (80, 'My Leave Balance', 79, 78, 3, 200, NULL, 0);";

        /** TODO: Improve here to support upgrading installs with modified user role tables. */        
        $sql[] = "DELETE FROM ohrm_user_role_screen";
        $sql[] = "ALTER TABLE ohrm_user_role_screen AUTO_INCREMENT = 0";
        $sql[] = "INSERT INTO ohrm_user_role_screen (user_role_id, screen_id, can_read, can_create, can_update, can_delete) VALUES
                    (1, 1, 1, 1, 1, 1),
                    (1, 2, 1, 1, 1, 1),
                    (2, 2, 0, 0, 0, 0),
                    (3, 2, 0, 0, 0, 0),
                    (1, 3, 1, 1, 1, 1),
                    (2, 3, 0, 0, 0, 0),
                    (3, 3, 0, 0, 0, 0),
                    (1, 4, 1, 1, 1, 1),
                    (1, 5, 1, 1, 1, 1),
                    (3, 5, 1, 0, 0, 0),
                    (1, 6, 1, 0, 0, 1),
                    (1, 7, 1, 1, 1, 1),
                    (1, 8, 1, 1, 1, 1),
                    (1, 9, 1, 1, 1, 1),
                    (1, 10, 1, 1, 1, 1),
                    (1, 11, 1, 1, 1, 1),
                    (1, 12, 1, 1, 1, 1),
                    (1, 13, 1, 1, 1, 1),
                    (1, 14, 1, 1, 1, 1),
                    (1, 16, 1, 1, 1, 0),
                    (3, 16, 1, 1, 1, 0),
                    (1, 17, 1, 1, 1, 0),
                    (3, 17, 1, 1, 1, 0),
                    (1, 18, 1, 1, 1, 0),
                    (2, 18, 1, 0, 0, 0),
                    (3, 18, 1, 0, 0, 0),
                    (1, 19, 1, 1, 1, 1),
                    (1, 20, 1, 1, 1, 1),
                    (1, 21, 1, 1, 1, 1),
                    (1, 22, 1, 1, 1, 1),
                    (1, 23, 1, 1, 1, 1),
                    (1, 24, 1, 1, 1, 1),
                    (1, 25, 1, 1, 1, 1),
                    (1, 26, 1, 1, 1, 1),
                    (1, 27, 1, 1, 1, 1),
                    (1, 28, 1, 1, 1, 1),
                    (1, 29, 1, 1, 1, 1),
                    (1, 30, 1, 1, 1, 1),
                    (1, 31, 1, 1, 1, 1),
                    (1, 32, 1, 1, 1, 1),
                    (1, 33, 1, 1, 1, 1),
                    (1, 34, 1, 1, 1, 1),
                    (1, 35, 1, 1, 1, 1),
                    (1, 36, 1, 1, 1, 1),
                    (1, 37, 1, 1, 1, 1),
                    (4, 37, 1, 0, 0, 0),
                    (1, 38, 1, 1, 1, 1),
                    (1, 39, 1, 1, 1, 1),
                    (1, 40, 1, 1, 1, 1),
                    (1, 41, 1, 1, 1, 1),
                    (1, 42, 1, 1, 1, 1),
                    (1, 43, 1, 1, 1, 1),
                    (1, 44, 1, 1, 1, 1),
                    (1, 45, 1, 1, 1, 1),
                    (2, 46, 1, 1, 1, 1),
                    (1, 47, 1, 1, 1, 1),
                    (2, 48, 1, 1, 1, 0),
                    (2, 49, 1, 1, 1, 1),
                    (1, 50, 1, 1, 1, 1),
                    (2, 50, 1, 0, 0, 0),
                    (2, 51, 1, 1, 1, 1),
                    (1, 52, 1, 1, 1, 1),
                    (3, 52, 1, 1, 1, 1),
                    (2, 53, 1, 1, 0, 0),
                    (2, 54, 1, 1, 1, 1),
                    (1, 55, 1, 1, 0, 1),
                    (3, 55, 1, 1, 0, 0),
                    (1, 56, 1, 1, 1, 1),
                    (1, 57, 1, 1, 1, 1),
                    (4, 57, 1, 1, 1, 1),
                    (1, 58, 1, 1, 1, 1),
                    (3, 58, 1, 1, 1, 1),
                    (1, 59, 1, 1, 1, 1),
                    (3, 59, 1, 1, 1, 1),
                    (1, 60, 1, 1, 1, 1),
                    (6, 60, 1, 1, 1, 1),
                    (5, 60, 1, 0, 1, 0),
                    (1, 61, 1, 1, 1, 1),
                    (1, 62, 1, 1, 1, 1),
                    (1, 63, 1, 1, 1, 1),
                    (1, 64, 1, 1, 1, 1),
                    (1, 65, 1, 1, 1, 1),
                    (1, 66, 1, 1, 1, 1),
                    (2, 66, 1, 0, 1, 0),
                    (7, 66, 1, 0, 1, 0),
                    (1, 67, 1, 1, 1, 1),
                    (2, 67, 1, 0, 1, 0),
                    (3, 67, 1, 0, 1, 0),
                    (1, 68, 1, 1, 1, 1),
                    (2, 68, 1, 0, 1, 0),
                    (3, 68, 1, 0, 1, 0),
                    (1, 69, 1, 1, 1, 1),
                    -- (2, 69, 0, 0, 0, 0),
                    (3, 69, 1, 0, 0, 0),
                    -- (1, 70, 0, 0, 0, 0),
                    (2, 70, 1, 0, 0, 0),
                    -- (3, 70, 0, 0, 0, 0),
                    (1, 71, 1, 0, 0, 1),
                    (1, 72, 1, 1, 1, 0),
                    (1, 73, 1, 0, 1, 0),
                    (1, 74, 1, 1, 1, 1),
                    (4, 74, 1, 1, 1, 1),
                    (1, 75, 1, 1, 1, 1),
                    (3, 75, 1, 1, 1, 1),
                    (1, 76, 1, 1, 1, 1),
                    (5, 76, 1, 1, 1, 1),
                    (6, 76, 1, 1, 1, 1),
                    (1, 77, 1, 1, 1, 1),
                    (2, 77, 1, 1, 1, 1),
                    (7, 77, 1, 1, 1, 1),
                    (1, 78, 1, 0, 0, 0),
                    (3, 78, 1, 0, 0, 0),
                    (2, 79, 1, 0, 0, 0);";
                               
        /* Importing leave type and entitlement data */
        $sql[] = "alter table `hs_hr_leavetype` add column int_id int not null auto_increment unique key;";

        $sql[] = "INSERT INTO `ohrm_leave_type` (`id`, `name`, `deleted`, `operational_country_id`)
                            SELECT old_lt.`int_id`, old_lt.`leave_type_name`, 
                            IF(old_lt.`available_flag` = 1, 0, 1) , old_lt.`operational_country_id`
                            FROM `hs_hr_leavetype` old_lt;";

        $sql[] = "INSERT INTO `ohrm_leave_entitlement`(emp_number, no_of_days, leave_type_id, from_date, to_date, 
                                        credited_date, note, entitlement_type, `deleted`)
                    SELECT q.employee_id, q.no_of_days_allotted, lt.int_id, p.leave_period_start_date, p.leave_period_end_date, 
                    p.leave_period_start_date, 'record created by upgrade', 1, 0
                    FROM `hs_hr_employee_leave_quota` q LEFT JOIN `hs_hr_leavetype` lt ON lt.leave_type_id = q.leave_type_id
                    LEFT JOIN hs_hr_leave_period p ON p.leave_period_id = q.leave_period_id;";

        $sql[] = "alter table `hs_hr_leavetype` drop column int_id;";

        $sql[] = "INSERT INTO `ohrm_leave_period`(leave_period_id, leave_period_start_date, leave_period_end_date) 
                    SELECT leave_period_id, leave_period_start_date, leave_period_end_date FROM hs_hr_leave_period";
        
        /* TODO: Import rest of leave data - leave requests etc. */
        
        $this->sql = $sql;
    }

    public function getNotes() {
        return array();
    }
}
