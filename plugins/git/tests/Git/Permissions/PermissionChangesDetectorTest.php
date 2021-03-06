<?php
/**
 * Copyright (c) Enalean, 2016. All Rights Reserved.
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Tuleap\Git\Permissions;

use TuleapTestCase;
use Git;

require_once dirname(__FILE__).'/../../bootstrap.php';

class PermissionChangesDetectorForRepositoryTest extends TuleapTestCase
{
    /**
     * @var PermissionChangesDetector
     */
    private $detector;

    public function setUp()
    {
        $this->git_permission_manager = mock('GitPermissionsManager');
        $this->retriever              = mock('Tuleap\Git\Permissions\FineGrainedRetriever');

        $this->detector = new PermissionChangesDetector(
            $this->git_permission_manager,
            $this->retriever
        );

        $this->branch_fine_grained_permission = new FineGrainedPermission(
            1,
            1,
            'refs/heads/master',
            array(),
            array()
        );

        $this->tag_fine_grained_permission = new FineGrainedPermission(
            2,
            1,
            'refs/tags/*',
            array(),
            array()
        );

        $this->repository = aGitRepository()->withId(1)->build();
    }

    public function itDetectsChangesIfABranchPermissionIsAdded()
    {
        stub($this->retriever)->doesRepositoryUseFineGrainedPermissions($this->repository)->returns(true);

        $has_changes = $this->detector->areThereChangesInPermissionsForRepository(
            $this->repository,
            array(),
            'on',
            array($this->branch_fine_grained_permission),
            array(),
            array()
        );

        $this->assertTrue($has_changes);
    }

    public function itDetectsChangesIfATagPermissionIsAdded()
    {
        stub($this->retriever)->doesRepositoryUseFineGrainedPermissions($this->repository)->returns(true);

        $has_changes = $this->detector->areThereChangesInPermissionsForRepository(
            $this->repository,
            array(),
            'on',
            array(),
            array($this->tag_fine_grained_permission),
            array()
        );

        $this->assertTrue($has_changes);
    }

    public function itDetectsChangesIfAtLeastOneFineGrainedPermissionIsUpdated()
    {
        stub($this->retriever)->doesRepositoryUseFineGrainedPermissions($this->repository)->returns(true);

        $has_changes = $this->detector->areThereChangesInPermissionsForRepository(
            $this->repository,
            array(),
            'on',
            array(),
            array(),
            array($this->tag_fine_grained_permission)
        );

        $this->assertTrue($has_changes);
    }

    public function itDetectsChangesIfFineGrainedPermissionAreEnabled()
    {
        stub($this->retriever)->doesRepositoryUseFineGrainedPermissions($this->repository)->returns(false);

        $has_changes = $this->detector->areThereChangesInPermissionsForRepository(
            $this->repository,
            array(),
            'on',
            array(),
            array(),
            array()
        );

        $this->assertTrue($has_changes);
    }

    public function itDetectsChangesIfFineGrainedPermissionAreDisabled()
    {
        stub($this->retriever)->doesRepositoryUseFineGrainedPermissions($this->repository)->returns(true);

        $has_changes = $this->detector->areThereChangesInPermissionsForRepository(
            $this->repository,
            array(),
            false,
            array(),
            array(),
            array()
        );

        $this->assertTrue($has_changes);
    }

    public function itDetectsChangesIfGlobalPermissionAreChanged()
    {
        stub($this->retriever)->doesRepositoryUseFineGrainedPermissions($this->repository)->returns(false);
        stub($this->git_permission_manager)->getRepositoryGlobalPermissions($this->repository)->returns(array(
            Git::PERM_READ => array('3'),
            Git::PERM_WRITE => array('4')
        ));

        $has_changes = $this->detector->areThereChangesInPermissionsForRepository(
            $this->repository,
            array(Git::PERM_READ => array('3', '101'), Git::PERM_WRITE => array('4')),
            false,
            array(),
            array(),
            array()
        );

        $this->assertTrue($has_changes);
    }

    public function itDoesNotDetectChangesIfNothingChangedWithFineGrainedPermissions()
    {
        stub($this->retriever)->doesRepositoryUseFineGrainedPermissions($this->repository)->returns(true);
        stub($this->git_permission_manager)->getRepositoryGlobalPermissions($this->repository)->returns(array(
            Git::PERM_READ => array('3')
        ));

        $has_changes = $this->detector->areThereChangesInPermissionsForRepository(
            $this->repository,
            array(Git::PERM_READ => array('3')),
            'on',
            array(),
            array(),
            array()
        );

        $this->assertFalse($has_changes);
    }

    public function itDoesNotDetectChangesIfNothingChangedWithoutFineGrainedPermissions()
    {
        stub($this->retriever)->doesRepositoryUseFineGrainedPermissions($this->repository)->returns(false);
        stub($this->git_permission_manager)->getRepositoryGlobalPermissions($this->repository)->returns(array(
            Git::PERM_READ => array('3'),
            Git::PERM_WRITE => array('4')
        ));

        $has_changes = $this->detector->areThereChangesInPermissionsForRepository(
            $this->repository,
            array(Git::PERM_READ => array('3'), Git::PERM_WRITE => array('4')),
            false,
            array(),
            array(),
            array()
        );

        $this->assertFalse($has_changes);
    }
}

class PermissionChangesDetectorForProjectTest extends TuleapTestCase
{
    public function setUp()
    {
        $this->git_permission_manager = mock('GitPermissionsManager');
        $this->retriever              = mock('Tuleap\Git\Permissions\FineGrainedRetriever');

        $this->detector = new PermissionChangesDetector(
            $this->git_permission_manager,
            $this->retriever
        );

        $this->project = stub('Project')->getId()->returns(101);

        $this->default_branch_fine_grained_permission = new DefaultFineGrainedPermission(
            1,
            101,
            'refs/heads/master',
            array(),
            array()
        );

        $this->default_tag_fine_grained_permission = new DefaultFineGrainedPermission(
            2,
            101,
            'refs/tags/*',
            array(),
            array()
        );
    }

    public function itDetectsChangesForProjectIfABranchPermissionIsAdded()
    {
        stub($this->retriever)->doesProjectUseFineGrainedPermissions($this->project)->returns(true);

        $has_changes = $this->detector->areThereChangesInPermissionsForProject(
            $this->project,
            array(),
            array(),
            array(),
            'on',
            array($this->default_branch_fine_grained_permission),
            array(),
            array()
        );

        $this->assertTrue($has_changes);
    }

    public function itDetectsChangesForProjectIfATagPermissionIsAdded()
    {
        stub($this->retriever)->doesProjectUseFineGrainedPermissions($this->project)->returns(true);

        $has_changes = $this->detector->areThereChangesInPermissionsForProject(
            $this->project,
            array(),
            array(),
            array(),
            'on',
            array(),
            array($this->default_tag_fine_grained_permission),
            array()
        );

        $this->assertTrue($has_changes);
    }

    public function itDetectsChangesForProjectIfAtLeastOneFineGrainedPermissionIsUpdated()
    {
        stub($this->retriever)->doesProjectUseFineGrainedPermissions($this->project)->returns(true);

        $has_changes = $this->detector->areThereChangesInPermissionsForProject(
            $this->project,
            array(),
            array(),
            array(),
            'on',
            array(),
            array(),
            array($this->default_branch_fine_grained_permission)
        );

        $this->assertTrue($has_changes);
    }

    public function itDetectsChangesForProjectIfFineGrainedPermissionAreEnabled()
    {
        stub($this->retriever)->doesProjectUseFineGrainedPermissions($this->project)->returns(false);

        $has_changes = $this->detector->areThereChangesInPermissionsForProject(
            $this->project,
            array(),
            array(),
            array(),
            'on',
            array(),
            array(),
            array()
        );

        $this->assertTrue($has_changes);
    }

    public function itDetectsChangesForProjectIfFineGrainedPermissionAreDisabled()
    {
        stub($this->retriever)->doesProjectUseFineGrainedPermissions($this->project)->returns(true);

        $has_changes = $this->detector->areThereChangesInPermissionsForProject(
            $this->project,
            array(),
            array(),
            array(),
            false,
            array(),
            array(),
            array()
        );

        $this->assertTrue($has_changes);
    }

    public function itDetectsChangesForProjectIfGlobalPermissionAreChanged()
    {
        stub($this->retriever)->doesProjectUseFineGrainedPermissions($this->project)->returns(false);
        stub($this->git_permission_manager)->getProjectGlobalPermissions($this->project)->returns(array(
            Git::DEFAULT_PERM_READ => array('3'),
            Git::DEFAULT_PERM_WRITE => array('4'),
            Git::DEFAULT_PERM_WPLUS => array(),
        ));

        $has_changes = $this->detector->areThereChangesInPermissionsForProject(
            $this->project,
            array('3', '101'),
            array('4'),
            array(),
            false,
            array(),
            array(),
            array()
        );

        $this->assertTrue($has_changes);
    }

    public function itDoesNotDetectChangesForProjectIfNothingChangedWithFineGrainedPermissions()
    {
        stub($this->retriever)->doesProjectUseFineGrainedPermissions($this->project)->returns(true);
        stub($this->git_permission_manager)->getProjectGlobalPermissions($this->project)->returns(array(
            Git::DEFAULT_PERM_READ => array('3')
        ));

        $has_changes = $this->detector->areThereChangesInPermissionsForProject(
            $this->project,
            array('3'),
            array(),
            array(),
            'on',
            array(),
            array(),
            array()
        );

        $this->assertFalse($has_changes);
    }

    public function itDoesNotDetectChangesForProjectIfNothingChangedWithoutFineGrainedPermissions()
    {
        stub($this->retriever)->doesProjectUseFineGrainedPermissions($this->project)->returns(false);
        stub($this->git_permission_manager)->getProjectGlobalPermissions($this->project)->returns(array(
            Git::DEFAULT_PERM_READ => array('3'),
            Git::DEFAULT_PERM_WRITE => array('4'),
            Git::DEFAULT_PERM_WPLUS => array(),
        ));

        $has_changes = $this->detector->areThereChangesInPermissionsForProject(
            $this->project,
            array('3'),
            array('4'),
            array(),
            false,
            array(),
            array(),
            array()
        );

        $this->assertFalse($has_changes);
    }
}
