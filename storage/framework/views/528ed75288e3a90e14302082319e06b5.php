<?php
    $notifications = [
        [
            'id' => 1,
            'userName' => 'Terry Franci',
            'userImage' => '/images/user/user-02.jpg',
            'action' => 'requests permission to change',
            'project' => 'Project - Nganter App',
            'type' => 'Project',
            'time' => '5 min ago',
            'status' => 'online',
        ],
        [
            'id' => 2,
            'userName' => 'Alex Johnson',
            'userImage' => '/images/user/user-03.jpg',
            'action' => 'requests permission to change',
            'project' => 'Project - Nganter App',
            'type' => 'Project',
            'time' => '10 min ago',
            'status' => 'offline',
        ],
        [
            'id' => 3,
            'userName' => 'Sarah Williams',
            'userImage' => '/images/user/user-04.jpg',
            'action' => 'requests permission to change',
            'project' => 'Project - Dashboard UI',
            'type' => 'Project',
            'time' => '15 min ago',
            'status' => 'online',
        ],
        [
            'id' => 4,
            'userName' => 'Mike Brown',
            'userImage' => '/images/user/user-05.jpg',
            'action' => 'requests permission to change',
            'project' => 'Project - E-commerce',
            'type' => 'Project',
            'time' => '20 min ago',
            'status' => 'online',
        ],
        [
            'id' => 5,
            'userName' => 'Emma Davis',
            'userImage' => '/images/user/user-06.jpg',
            'action' => 'requests permission to change',
            'project' => 'Project - Mobile App',
            'type' => 'Project',
            'time' => '25 min ago',
            'status' => 'offline',
        ],
        [
            'id' => 6,
            'userName' => 'John Smith',
            'userImage' => '/images/user/user-07.jpg',
            'action' => 'requests permission to change',
            'project' => 'Project - Landing Page',
            'type' => 'Project',
            'time' => '30 min ago',
            'status' => 'online',
        ],
        [
            'id' => 7,
            'userName' => 'Lisa Anderson',
            'userImage' => '/images/user/user-08.jpg',
            'action' => 'requests permission to change',
            'project' => 'Project - Blog System',
            'type' => 'Project',
            'time' => '35 min ago',
            'status' => 'online',
        ],
        [
            'id' => 8,
            'userName' => 'David Wilson',
            'userImage' => '/images/user/user-09.jpg',
            'action' => 'requests permission to change',
            'project' => 'Project - CRM Dashboard',
            'type' => 'Project',
            'time' => '40 min ago',
            'status' => 'online',
        ],
    ];
?>

<?php if (isset($component)) { $__componentOriginalaa2b5aa64a35219d0252f47520c1a16c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalaa2b5aa64a35219d0252f47520c1a16c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.dropdown','data' => ['align' => 'right','width' => '350px']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.dropdown'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['align' => 'right','width' => '350px']); ?>
     <?php $__env->slot('trigger', null, []); ?> 
        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'bell','class' => 'w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'bell','class' => 'w-5 h-5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $attributes = $__attributesOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $component = $__componentOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__componentOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
        <span class="edz-topbar__dot"></span>
     <?php $__env->endSlot(); ?>

    <div class="edz-dropdown__header">
        <h5 class="edz-dropdown__title"><?php echo e(__('menu.notifications')); ?></h5>
    </div>

    <ul class="edz-notifications">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li class="edz-notifications__item">
                <a href="#" class="edz-notifications__link">
                    <span class="edz-notifications__avatar">
                        <img src="<?php echo e($notification['userImage']); ?>" alt="<?php echo e($notification['userName']); ?>" />
                        <span class="edz-notifications__status edz-notifications__status--<?php echo e($notification['status']); ?>"></span>
                    </span>
                    <span class="edz-notifications__content">
                        <span class="edz-notifications__text">
                            <span class="edz-notifications__user"><?php echo e($notification['userName']); ?></span>
                            <?php echo e($notification['action']); ?>

                            <span class="edz-notifications__project"><?php echo e($notification['project']); ?></span>
                        </span>
                        <span class="edz-notifications__meta">
                            <span><?php echo e($notification['type']); ?></span>
                            <span class="edz-notifications__separator"></span>
                            <span><?php echo e($notification['time']); ?></span>
                        </span>
                    </span>
                </a>
            </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </ul>

    <a href="#" class="edz-dropdown__footer">
        <?php echo e(__('buttons.view_all')); ?> <?php echo e(__('menu.notifications')); ?>

    </a>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalaa2b5aa64a35219d0252f47520c1a16c)): ?>
<?php $attributes = $__attributesOriginalaa2b5aa64a35219d0252f47520c1a16c; ?>
<?php unset($__attributesOriginalaa2b5aa64a35219d0252f47520c1a16c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalaa2b5aa64a35219d0252f47520c1a16c)): ?>
<?php $component = $__componentOriginalaa2b5aa64a35219d0252f47520c1a16c; ?>
<?php unset($__componentOriginalaa2b5aa64a35219d0252f47520c1a16c); ?>
<?php endif; ?>
<?php /**PATH C:\laragon\www\edzeery\resources\views/components/edz/notification-dropdown.blade.php ENDPATH**/ ?>