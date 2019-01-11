from django.db import models

class monitoring(models.Model):
    id = models.AutoField(primary_key=True)
    type = models.IntegerField(null=False)
    table_name = models.CharField(max_length=64)
    table_time = models.DateTimeField(null=False)
    create_time = models.DateTimeField(null=False)

    indexes = [
        models.Index(fields=['id']),
        models.Index(fields=['type']),
        models.Index(fields=['table_name']),
        models.Index(fields=['create_time']),
    ]

    class Meta:
        app_label = "default"
        db_table = "monitoring"

