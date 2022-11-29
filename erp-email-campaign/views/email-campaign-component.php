<i class="fa fa-envelope-o"></i>
<div class="timeline-item timeline-oneliner" id="timeline-item-{{ feed.id }}">
    <div class="timeline-body">
        <div class="timeline-email-body">
            <div class="timeline-feed-header-text"><strong v-if="isActivityPage">{{ createdForUser }}:</strong> {{{ feed.message }}}</div>
        </div>
    </div>
</div>
